<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\FormSubmission;
use App\Models\Signatory;

use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FacilitiesFormPdfController extends Controller
{
    /**
     * Generate a Facilities and Utilization Form PDF from an approved FormSubmission.
     *
     * This uses the same DOCX template approach as GsuFormController,
     * but fills values from the JSON payload instead of form inputs.
     */
    public function generate(FormSubmission $submission): BinaryFileResponse
    {
        if ($submission->type !== 'facilities_utilization' || ! in_array($submission->status, ['approved', 'reserved'], true)) {
            abort(404);
        }



        $payload = $submission->payload ?? [];
        $facilityIds = $payload['facility_ids'] ?? (isset($payload['facility_id']) ? [$payload['facility_id']] : []);
        $facilitiesById = Facility::whereIn('id', $facilityIds)->get()->keyBy('id');
        $facilities = collect($facilityIds)->map(fn ($id) => $facilitiesById->get($id))->filter()->values();

        // Map payload to template placeholders
        $controlNo      = $payload['control_no']    ?? '';
        $dateRequest    = $payload['date_request']  ?? now()->toDateString();
        $requesterName  = $payload['requester_name'] ?? ($submission->requester->name ?? '');
        $dateActivity   = $payload['date_activity'] ?? '';
        if (! empty($payload['date_end']) && $payload['date_end'] !== $dateActivity) {
            $dateActivity .= ' to ' . $payload['date_end'];
        }
        $timeRange      = $payload['time_range'] ?? [];
        $timeActivity   = '';
        if (!empty($timeRange['start']) && !empty($timeRange['end'])) {
            $timeActivity = $timeRange['start'].' - '.$timeRange['end'];
        }
        $purpose        = $payload['purpose'] ?? '';

        $equipment = $payload['equipment'] ?? [];

        $notedName = $payload['noted_signatory_name'] ?? '';
        $notedDatetime = $payload['noted_datetime'] ?? '';
        $approvedName = $payload['approved_head_name'] ?? 'GSU ORG HEAD';
        $approvedDatetime = $payload['approved_datetime'] ?? '';




        // Map facility names to template checkbox placeholders.
        // IMPORTANT: placeholders must match the DOCX template (ex: PAGHIUSA HALL / PAGHIUSA).
        $facilityToKeyMap = [
            'BUSALIAN HALL'   => 'busalian_hall',
            'PAGHIUSA HALL'   => 'paghiusa_hall',
            'E-HUB'           => 'ehub',
            'BALAY NI JUAN'   => 'balay_ni_juan',
            'ICT AVR'         => 'ict_avr',
            'CEA AVR'         => 'cea_avr',
            'CBA AVR'         => 'cba_avr',
            'NEW AVR'         => 'new_avr',
            'GRAND STAND'     => 'grandstand',
            'COVERED GYM'     => 'covered_gym',
            'TRACK OVAL'      => 'track_oval',
        ];

        $checked = '✔';
        $unchecked = '';

        $venueCheckboxes = [
            'busalian_hall'  => $unchecked,
            'paghiusa_hall'  => $unchecked,
            'ehub'           => $unchecked,
            'balay_ni_juan' => $unchecked,
            'ict_avr'       => $unchecked,
            'cea_avr'       => $unchecked,
            'cba_avr'       => $unchecked,
            'new_avr'       => $unchecked,
            'grandstand'    => $unchecked,
            'covered_gym'   => $unchecked,
            'track_oval'    => $unchecked,
            'others'        => $unchecked,
        ];

        $venueOthersText = $payload['venue_others'] ?? '';

        $otherFacilityNames = [];
        foreach ($facilities as $facility) {
            $facilityName = strtoupper(trim($facility->name));
            if (array_key_exists($facilityName, $facilityToKeyMap)) {
                $venueCheckboxes[$facilityToKeyMap[$facilityName]] = $checked;
            } else {
                $venueCheckboxes['others'] = $checked;
                $otherFacilityNames[] = $facility->name;
            }
        }

        if (!empty($payload['venue_others'])) {
            $venueCheckboxes['others'] = $checked;
            $otherFacilityNames[] = $payload['venue_others'];
        }
        $venueOthersText = implode(', ', array_unique($otherFacilityNames)) ?: $venueOthersText;

        // Load the DOCX template (support app/templates and storage/app/templates)
        $templatePath = base_path('app/templates/FACILITIES-AND-UTILIZATION-FORM-TEMPLATE.docx');
        if (!file_exists($templatePath)) {
            $templatePath = storage_path('app/templates/FACILITIES-AND-UTILIZATION-FORM-TEMPLATE.docx');
        }

        if (!file_exists($templatePath)) {
            abort(500, 'Facilities Utilization Form template not found.');
        }

        $template = new TemplateProcessor($templatePath);

        // Set values
        $template->setValue('control_no',       $controlNo);
        $template->setValue('date_request',     $dateRequest);
        $template->setValue('requester_name',   $requesterName);
        // Contact is not part of the digital form; leave blank
        $template->setValue('requester_contact',$payload['requester_contact'] ?? '');
        $template->setValue('date_activity',    $dateActivity);
        $template->setValue('time_activity',    $timeActivity);
        $template->setValue('purpose',          $purpose);

        // Venue checkbox values
        $template->setValue('busalian_hall', $venueCheckboxes['busalian_hall'] ?? '');
        $template->setValue('paghiusa_hall', $venueCheckboxes['paghiusa_hall'] ?? '');
        $template->setValue('ehub',          $venueCheckboxes['ehub'] ?? '');
        $template->setValue('balay_ni_juan', $venueCheckboxes['balay_ni_juan'] ?? '');
        $template->setValue('ict_avr',       $venueCheckboxes['ict_avr'] ?? '');
        $template->setValue('cea_avr',       $venueCheckboxes['cea_avr'] ?? '');
        $template->setValue('cba_avr',       $venueCheckboxes['cba_avr'] ?? '');
        $template->setValue('new_avr',       $venueCheckboxes['new_avr'] ?? '');
        $template->setValue('grandstand',    $venueCheckboxes['grandstand'] ?? '');
        $template->setValue('covered_gym',   $venueCheckboxes['covered_gym'] ?? '');
        $template->setValue('track_oval',    $venueCheckboxes['track_oval'] ?? '');
        $template->setValue('others',        $venueCheckboxes['others'] ?? '');
        $template->setValue('venue_others',  $venueOthersText);

        // Equipment quantities (align with FORMREADME placeholders)
        $template->setValue('qty_monobloc', $equipment['monobloc_chair'] ?? '');
        $template->setValue('qty_table',    $equipment['table'] ?? '');
        $template->setValue('qty_fan',      $equipment['electric_fan'] ?? '');
        $template->setValue('qty_rostrum',  $equipment['rostrum'] ?? '');
        $template->setValue('qty_flag',     $equipment['flag'] ?? '');
        $template->setValue('qty_sound',    $equipment['sound'] ?? '');
        $template->setValue('qty_led',      $equipment['led'] ?? '');

        // Signature blocks – as per your requirement, leave signatures blank;
        // set req_name and req_datetime from system.
        $template->setValue('req_signature',      '');
        $template->setValue('req_name',           $requesterName);
        $template->setValue('req_datetime',       now()->format('M d, Y h:i A'));
        $template->setValue('noted_signature',    '');
        $template->setValue('noted_name',         $notedName);
        $template->setValue('noted_datetime',     $notedDatetime);
        $template->setValue('approved_signature', '');
        $template->setValue('approved_name',      $approvedName);
        $template->setValue('approved_datetime',  $approvedDatetime);


        // Save DOCX to temp path
        $docxPath = tempnam(sys_get_temp_dir(), 'facilities_') . '.docx';
        $template->saveAs($docxPath);

        // Convert to PDF using LibreOffice, like in GsuFormController
        return $this->convertAndDownload($docxPath, 'Facilities-Utilization-Form-'.$submission->id);
    }

    /**
     * DOCX → PDF via LibreOffice (shared with GsuFormController behavior).
     */
    private function convertAndDownload(string $docxPath, string $baseName): BinaryFileResponse
    {
        $outDir  = sys_get_temp_dir();
        $pdfPath = $outDir . '/' . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        // Convert with LibreOffice headless
        $cmd = sprintf(
            'soffice --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($outDir),
            escapeshellarg($docxPath)
        );
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($pdfPath)) {
            // Fallback: return the filled DOCX if PDF conversion fails
            return response()
                ->download($docxPath, $baseName . '.docx')
                ->deleteFileAfterSend(true);
        }

        // Remove the temp DOCX file
        @unlink($docxPath);

        // Display inline so the PDF opens in the browser's viewer instead of downloading
        return response()
            ->file($pdfPath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $baseName . '.pdf"',
            ])
            ->deleteFileAfterSend(true);
    }
}