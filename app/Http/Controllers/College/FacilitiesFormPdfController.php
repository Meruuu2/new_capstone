<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Admin\FacilitiesFormPdfController as AdminFacilitiesFormPdfController;
use App\Models\FormSubmission;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FacilitiesFormPdfController extends AdminFacilitiesFormPdfController
{
    /**
     * Allow college staff to download the approved PDF for their own request only.
     */
    public function generate(FormSubmission $submission): BinaryFileResponse
    {
        $user = request()->user();

        if ($submission->type !== 'facilities_utilization') {
            abort(404);
        }

        // Only approved (not converted)
        if ($submission->status !== 'approved') {
            abort(403);
        }

        // Ownership boundary: requester_id must be the logged-in staff.
        if ($submission->requester_id !== $user->id) {
            abort(403);
        }

        return parent::generate($submission);
    }
}


