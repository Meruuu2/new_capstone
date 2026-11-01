<?php

namespace Tests\Feature;

use App\Models\FormSubmission;
use App\Http\Controllers\PaymentReceiptController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentReceiptDownloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('payment_receipts');
    }

    public function test_public_requester_can_download_receipt_without_publicly_exposing_the_file(): void
    {
        $submission = $this->submission();
        Storage::disk('payment_receipts')->put('receipts/receipt.pdf', '%PDF-1.4 receipt');
        session()->put('public_ticket_ids', [$submission->id]);

        $response = app(PaymentReceiptController::class)->show($submission);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('max-age=0', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('receipt.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_receipt_download_is_not_available_without_requester_authorization(): void
    {
        $submission = $this->submission();
        Storage::disk('payment_receipts')->put('receipts/receipt.pdf', '%PDF-1.4 receipt');

        $this->expectException(NotFoundHttpException::class);
        app(PaymentReceiptController::class)->show($submission);
    }

    public function test_authorized_requester_can_download_a_submission_attachment(): void
    {
        $submission = $this->submission();
        $payload = $submission->payload;
        $payload['attachments'] = ['attachments/support.pdf'];
        $submission->payload = $payload;
        Storage::disk('payment_receipts')->put('attachments/support.pdf', '%PDF-1.4 support');
        session()->put('public_ticket_ids', [$submission->id]);

        $response = app(PaymentReceiptController::class)->attachment($submission, 0);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('support.pdf', $response->headers->get('Content-Disposition'));
    }

    private function submission(): FormSubmission
    {
        $submission = new FormSubmission([
            'type' => 'facilities_utilization',
            'requester_id' => 99,
            'requester_type' => 'guest',
            'requester_unit' => 'Test organization',
            'status' => 'pending_payment',
            'payload' => [],
            'payment_attachment' => 'receipts/receipt.pdf',
            'payment_status' => 'payment_uploaded',
        ]);
        $submission->setAttribute('id', 123);

        return $submission;
    }
}
