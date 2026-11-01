<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Services\GuestFacilitiesDocxFiller;
use App\Support\VenueRates;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Throwable;

class FacilitiesFormPdfController extends Controller
{
    /**
     * Generate a Guest Facilities and Utilization Form PDF from an approved FormSubmission.
     *
     * Uses the GUEST-FACILITIES-AND-UTILIZATION-FROM-TEMPLATE.docx template
     * and fills values from the JSON payload.
     */
    public function generate(Request $request, FormSubmission $submission): BinaryFileResponse
    {
        // Only allow PDF generation for approved guest submissions
        if ($submission->type !== 'facilities_utilization'
            || ! in_array($submission->status, ['approved', 'reserved'], true)) {
            abort(403, 'PDF generation is only available for approved requests.');
        }

        // Verify the user owns this submission or is an admin
        $user = $request->user();
        if (! $request->hasValidSignature(false) && ! ($user?->role === 'admin' || $submission->requester_id === $user?->id)) {
            abort(403, 'Unauthorized access.');
        }

        $payload = $submission->payload ?? [];
        $facilityIds = $payload['facility_ids'] ?? (isset($payload['facility_id']) ? [$payload['facility_id']] : []);
        $facilitiesById = Facility::whereIn('id', $facilityIds)->get()->keyBy('id');
        $facilities = collect($facilityIds)->map(fn ($id) => $facilitiesById->get($id))->filter()->values();

        $rateByName = collect(VenueRates::all())
            ->mapWithKeys(function (array $venue) {
                $name = $this->normalizeName($venue['name']);
                preg_match('/PhP\s*([\d,.]+)/i', $venue['rate'], $matches);

                return [$name => isset($matches[1]) ? (float) str_replace(',', '', $matches[1]) : null];
            });
        $startDate = $payload['date_activity'] ?? '';
        $endDate = $payload['date_end'] ?? $startDate;
        $days = $startDate
            ? max(1, \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1)
            : 1;
        $timeRange = $payload['time_range'] ?? [];
        $hours = ! empty($timeRange['start']) && ! empty($timeRange['end'])
            ? round(\Carbon\Carbon::parse('2000-01-01 ' . $timeRange['start'])
                ->diffInMinutes(\Carbon\Carbon::parse('2000-01-01 ' . $timeRange['end'])) / 60, 2)
            : null;
        $facilityRows = $facilities->map(function (Facility $facility) use ($rateByName, $days, $hours) {
            $rate = $rateByName->get($this->normalizeName($facility->name));

            return [
                'name' => $facility->name,
                'quantity' => '1',
                'rate' => $rate,
                'days' => (string) $days,
                'hours' => $hours === null ? '' : rtrim(rtrim(number_format($hours, 2, '.', ''), '0'), '.'),
                'total' => $rate === null ? null : $rate * $days,
            ];
        })->all();
        $rentalTotal = collect($facilityRows)->sum(fn (array $row) => $row['total'] ?? 0);

        $controlNo = $payload['control_no'] ?? '';
        $dateRequest = $payload['date_request'] ?? now()->toDateString();
        $clientName = $payload['client_name'] ?? '';
        $contactNumber = $payload['contact_number'] ?? '';
        $email = $payload['email'] ?? '';
        $organization = $payload['organization'] ?? '';
        $address = $payload['address'] ?? '';
        $purpose = $payload['purpose'] ?? '';
        $equipment = $payload['equipment'] ?? [];
        $notedName = $payload['noted_signatory_name'] ?? $payload['noted_signatory_custom'] ?? '';

        // Load the Guest DOCX template
        $templatePath = base_path('FORMS/GUEST-FACILITIES-AND-UTILIZATION-FROM-TEMPLATE.docx');
        if (!file_exists($templatePath)) {
            $templatePath = storage_path('app/templates/GUEST-FACILITIES-AND-UTILIZATION-FROM-TEMPLATE.docx');
        }

        if (!file_exists($templatePath)) {
            abort(500, 'Guest Facilities Utilization Form template not found.');
        }

        $temporaryBase = tempnam(sys_get_temp_dir(), 'guest_facilities_');
        @unlink($temporaryBase);
        $docxPath = $temporaryBase . '.docx';
        if (! copy($templatePath, $docxPath)) {
            abort(500, 'Could not copy the guest form template.');
        }
        app(GuestFacilitiesDocxFiller::class)->fill($docxPath, [
            'control_no' => $controlNo ?: ('FM-' . ($submission->created_at?->format('Y') ?? now()->format('Y')) . '-' . str_pad((string) $submission->id, 5, '0', STR_PAD_LEFT)),
            'date_request' => $dateRequest,
            'client_name' => $clientName,
            'contact_number' => $contactNumber,
            'email' => $email,
            'address' => $address,
            'organization' => $organization,
            'facility_names' => $facilities->pluck('name')->all(),
            'date_activity' => $startDate,
            'date_end' => $endDate,
            'time_range' => $timeRange,
            'purpose' => $purpose,
            'equipment' => $equipment,
            'noted_name' => $notedName,
            'facility_rows' => $facilityRows,
            'rental_total' => $rentalTotal,
        ]);

        // Convert to PDF using LibreOffice
        return $this->convertAndDownload($docxPath, 'Guest-Facilities-Form-'.$controlNo);
    }

    /**
     * DOCX → PDF via LibreOffice (same as admin controller).
     */
    private function normalizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9]+/', ' ', strtolower(trim($name)));
    }

    private function convertAndDownload(string $docxPath, string $baseName): BinaryFileResponse
    {
        $outDir  = sys_get_temp_dir();
        $pdfPath = $outDir . '/' . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        try {
            $process = new Process(['soffice', '--headless', '--convert-to', 'pdf', '--outdir', $outDir, $docxPath]);
            $process->setTimeout(60);
            $process->run();
        } catch (Throwable) {
        }

        if (! file_exists($pdfPath)) {
            $htmlPath = tempnam(sys_get_temp_dir(), 'guest_form_html_') . '.html';
            try {
                $word = IOFactory::load($docxPath);
                IOFactory::createWriter($word, 'HTML')->save($htmlPath);
                $html = file_get_contents($htmlPath);
                $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false]);
                $dompdf->loadHtml($html, 'UTF-8');
                $dompdf->setPaper('A4');
                $dompdf->render();
                file_put_contents($pdfPath, $dompdf->output());
            } catch (Throwable $exception) {
                report($exception);
            } finally {
                @unlink($htmlPath);
            }
        }

        if (!file_exists($pdfPath)) {
            // Fallback: return the filled DOCX if PDF conversion fails
            return response()
                ->download($docxPath, $baseName . '.docx')
                ->deleteFileAfterSend(true);
        }

        // Remove the temp DOCX file
        @unlink($docxPath);

        // Display inline so the PDF opens in the browser's viewer
        return response()
            ->file($pdfPath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $baseName . '.pdf"',
            ])
            ->deleteFileAfterSend(true);
    }
}
