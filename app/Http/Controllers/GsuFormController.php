<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\ConvertsToPdf;
use App\Models\FormControl;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GsuFormController extends Controller
{
    use ConvertsToPdf;

    // ----- FACILITIES AND UTILIZATION FORM -----

    public function showFacilities()
    {
        return view('forms.facilities');
    }

    public function downloadFacilities(Request $request): BinaryFileResponse
    {
        $request->validate([
            'date_request'      => 'required|date',
            'requester_name'    => 'required|string|max:255',
            'requester_contact' => 'nullable|string|max:50',
            'date_activity'     => 'required|date',
            'time_activity'     => 'required|string|max:100',
            'purpose'           => 'required|string|max:500',
        ]);

        $venueLabels = [
            'busalian_hall'  => 'BUSALIAN HALL',
            'paghiusa_hall'  => 'PAGHIUSA HALL',
            'e_hub'          => 'E-HUB',
            'balay_ni_juan'  => 'BALAY NI JUAN',
            'ict_avr'        => 'ICT AVR',
            'cea_avr'        => 'CEA AVR',
            'cba_avr'        => 'CBA AVR',
            'new_avr'        => 'NEW AVR',
            'grand_stand'    => 'GRAND STAND',
            'covered_gym'    => 'COVERED GYM',
            'track_oval'     => 'TRACK OVAL',
        ];
        $selectedVenues = [];
        foreach ($venueLabels as $key => $label) {
            if ($request->boolean($key)) {
                $selectedVenues[] = $label;
            }
        }

        $templatePath = base_path('app/templates/FACILITIES-AND-UTILIZATION-FORM-TEMPLATE.docx');
        if (!file_exists($templatePath)) {
            $templatePath = storage_path('app/templates/FACILITIES-AND-UTILIZATION-FORM-TEMPLATE.docx');
        }

        if (!file_exists($templatePath)) {
            abort(500, 'Facilities Utilization Form template not found.');
        }

        $template = new TemplateProcessor($templatePath);

        $controlNo = $request->input('control_no', $this->generateControlNumber('facilities'));

        $template->setValue('control_no',        $controlNo);
        $template->setValue('date_request',      $request->input('date_request'));
        $template->setValue('requester_name',    $request->input('requester_name'));
        $template->setValue('requester_contact', $request->input('requester_contact', ''));
        $template->setValue('date_activity',     $request->input('date_activity'));
        $template->setValue('time_activity',     $request->input('time_activity'));
        $template->setValue('purpose',           $request->input('purpose'));
        $template->setValue('venues_selected',   implode(', ', $selectedVenues));
        $template->setValue('venue_others',      $request->input('venue_others', ''));

        $template->setValue('qty_monobloc',      $request->input('qty_monobloc', ''));
        $template->setValue('qty_table',         $request->input('qty_table', ''));
        $template->setValue('qty_fan',           $request->input('qty_fan', ''));
        $template->setValue('qty_rostrum',       $request->input('qty_rostrum', ''));
        $template->setValue('qty_flag',          $request->input('qty_flag', ''));
        $template->setValue('qty_sound',         $request->input('qty_sound', ''));
        $template->setValue('qty_led',           $request->input('qty_led', ''));

        $template->setValue('req_signature',      '');
        $template->setValue('req_name',           $request->input('req_name', $request->input('requester_name')));
        $template->setValue('req_datetime',       now()->format('F d, Y  h:i A'));
        $template->setValue('noted_signature',    '');
        $template->setValue('noted_name',         $request->input('noted_name', ''));
        $template->setValue('noted_datetime',     '');
        $template->setValue('approved_signature', '');
        $template->setValue('approved_name',      $request->input('approved_name', ''));
        $template->setValue('approved_datetime',  '');

        $docxPath = tempnam(sys_get_temp_dir(), 'facilities_') . '.docx';
        $template->saveAs($docxPath);

        return $this->convertAndDownload($docxPath, 'Facilities-Utilization-Form');
    }

    // ----- REPAIR AND MAINTENANCE FORM -----

    public function showRepair()
    {
        return view('forms.repair');
    }

    public function downloadRepair(Request $request): BinaryFileResponse
    {
        $request->validate([
            'date_request'       => 'required|date',
            'requester_name'     => 'required|string|max:255',
            'department'         => 'required|string|max:255',
            'requester_contact'  => 'nullable|string|max:50',
            'repair_location'    => 'required|string|max:255',
            'problem_description'=> 'required|string|max:1000',
        ]);

        $check  = fn(bool $v) => $v ? '[✓]' : '[ ]';
        $method = $request->input('submission_method', '');

        $template = new TemplateProcessor(
            storage_path('app/templates/REPAIR-AND-MAINTENANCE-FORM-TEMPLATE.docx')
        );

        $controlNo = $request->input('control_no', $this->generateControlNumber('repair'));

        $template->setValue('control_no',           $controlNo);
        $template->setValue('date_request',          $request->input('date_request'));
        $template->setValue('requester_name',        $request->input('requester_name'));
        $template->setValue('department',            $request->input('department'));
        $template->setValue('requester_contact',     $request->input('requester_contact', ''));
        $template->setValue('repair_location',       $request->input('repair_location'));
        $template->setValue('cb_inperson',           $check($method === 'in-person'));
        $template->setValue('cb_phone',              $check($method === 'phone'));
        $template->setValue('cb_maintenance',        $check($request->boolean('type_maintenance')));
        $template->setValue('cb_repair',             $check($request->boolean('type_repair')));
        $template->setValue('cb_other_services',     $check($request->boolean('type_other')));
        $template->setValue('problem_description',   $request->input('problem_description'));
        $template->setValue('cb_approved',           '[ ]');
        $template->setValue('cb_disapproved',        '[ ]');
        $template->setValue('preferred_time',        $request->input('preferred_time', ''));
        $template->setValue('gsu_personnel',         '');
        $template->setValue('worker_signature',      '');
        $template->setValue('worker_name',           '');
        $template->setValue('worker_datetime',       '');
        $template->setValue('remarks',               '');
        $template->setValue('enduser_signature',     '');
        $template->setValue('enduser_name',          $request->input('requester_name'));
        $template->setValue('enduser_datetime',      '');

        $docxPath = tempnam(sys_get_temp_dir(), 'repair_') . '.docx';
        $template->saveAs($docxPath);

        return $this->convertAndDownload($docxPath, 'Repair-Maintenance-Form');
    }

    // ----- HELPER: Generate Control Number -----

    private function generateControlNumber(string $formType): string
    {
        $datePart = now()->format('Ymd');

        $lastControlNo = optional(FormControl::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first())->control_number;

        if ($lastControlNo && str_starts_with($lastControlNo, 'GSU-' . $datePart)) {
            $lastSeq = (int) substr($lastControlNo, -4);
            $newSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSeq = '0001';
        }

        $newControlNo = 'GSU-' . $datePart . '-' . $newSeq;

        FormControl::create([
            'control_number' => $newControlNo,
            'form_type' => $formType,
        ]);

        return $newControlNo;
    }
}
