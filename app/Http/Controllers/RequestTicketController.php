<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Dompdf\Dompdf;

class RequestTicketController extends Controller
{
    /** Facility request ticket as a PDF download. The route is signed: only someone shown the ticket has the link. */
    public function download(FormSubmission $submission)
    {
        abort_unless($submission->isFacilitiesUtilization(), 404);

        $data = $submission->ticketData();
        $logo = public_path('img/facilities/UA-logo.png');
        $data['logo'] = is_file($logo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logo)) : null;
        $data['submission'] = $submission;

        $dompdf = new Dompdf(['isRemoteEnabled' => false, 'defaultFont' => 'DejaVu Sans']);
        $dompdf->loadHtml(view('pdf.request-ticket', $data)->render(), 'UTF-8');
        $dompdf->setPaper('A5');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $data['ticketNo'] . '.pdf"',
        ]);
    }
}
