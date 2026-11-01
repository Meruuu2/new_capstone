<?php

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentReceiptController extends Controller
{
    public function show(FormSubmission $submission): StreamedResponse
    {
        $this->authorizeDownload($submission);
        abort_unless($submission->payment_attachment, 404);

        return $this->download($submission->payment_attachment, 'payment_attachments/');
    }

    public function attachment(FormSubmission $submission, int $index): StreamedResponse
    {
        $this->authorizeDownload($submission);

        $path = $submission->payload['attachments'][$index] ?? null;
        abort_unless(is_string($path), 404);

        return $this->download($path, 'attachments/');
    }

    private function authorizeDownload(FormSubmission $submission): void
    {
        abort_unless($submission->type === 'facilities_utilization', 404);

        $user = auth()->user();
        $isAdmin = $user?->isAdmin() ?? false;
        $isRequester = $user && (int) $submission->requester_id === (int) $user->id;
        $publicTicketIds = array_map('intval', session('public_ticket_ids', []));
        $isPublicRequester = in_array((int) $submission->id, $publicTicketIds, true);

        abort_unless($isAdmin || $isRequester || $isPublicRequester, 404);
    }

    private function download(string $path, string $legacyPrefix): StreamedResponse
    {
        $disk = Storage::disk('payment_receipts');

        if (! $disk->exists($path)) {
            abort_unless(str_starts_with($path, $legacyPrefix) && ! str_contains($path, '..'), 404);
            $disk = Storage::disk('public');
        }

        abort_unless($disk->exists($path), 404);

        return $disk->download($path, basename($path), [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Content-Security-Policy' => 'sandbox',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
