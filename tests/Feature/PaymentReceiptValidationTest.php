<?php

namespace Tests\Feature;

use App\Http\Requests\StoreFacilitiesUtilizationRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PaymentReceiptValidationTest extends TestCase
{
    public function test_public_requests_do_not_require_a_payment_receipt_before_approval(): void
    {
        $this->assertFalse($this->receiptValidator()->fails());
    }

    public function test_public_requests_cannot_submit_a_receipt_before_approval(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'receipt.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n"
        );

        $this->assertTrue($this->receiptValidator($file)->fails());
    }

    public function test_public_requests_reject_receipts_larger_than_ten_megabytes(): void
    {
        $content = "%PDF-1.4\n" . str_repeat('0', 10 * 1024 * 1024);
        $file = UploadedFile::fake()->createWithContent('receipt.pdf', $content);

        $this->assertTrue($this->receiptValidator($file)->fails());
    }

    public function test_public_requests_reject_unsupported_receipt_types(): void
    {
        $file = UploadedFile::fake()->createWithContent('receipt.txt', 'payment receipt');

        $this->assertTrue($this->receiptValidator($file)->fails());
    }

    public function test_supporting_documents_accept_jpg_png_and_pdf(): void
    {
        $files = [
            UploadedFile::fake()->image('scan.jpg'),
            UploadedFile::fake()->image('scan.png'),
            UploadedFile::fake()->createWithContent('support.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n"),
        ];

        $this->assertFalse($this->attachmentsValidator($files)->fails());
    }

    public function test_supporting_documents_reject_more_than_ten_files(): void
    {
        $files = array_map(
            fn ($index) => UploadedFile::fake()->createWithContent(
                "support-{$index}.pdf",
                "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n"
            ),
            range(1, 11)
        );

        $this->assertTrue($this->attachmentsValidator($files)->fails());
    }

    public function test_supporting_documents_reject_unsupported_file_types(): void
    {
        $file = UploadedFile::fake()->createWithContent('support.txt', 'not a supported document');

        $this->assertTrue($this->attachmentsValidator([$file])->fails());
    }

    public function test_receipts_use_a_private_local_disk_and_generated_filename(): void
    {
        $disk = config('filesystems.disks.payment_receipts');
        $this->assertSame('local', $disk['driver']);
        $this->assertSame('private', $disk['visibility']);
        $this->assertNotSame(storage_path('app/public'), $disk['root']);

        Storage::fake('payment_receipts');
        $file = UploadedFile::fake()->createWithContent(
            'original-receipt.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n"
        );
        $path = $file->store('receipts', 'payment_receipts');

        $this->assertStringStartsWith('receipts/', $path);
        $this->assertStringNotContainsString('original-receipt', $path);
        $this->assertTrue(Storage::disk('payment_receipts')->exists($path));
    }

    private function receiptValidator(?UploadedFile $file = null)
    {
        $request = StoreFacilitiesUtilizationRequest::create(
            '/request-facility',
            'POST',
            [],
            [],
            $file ? ['payment_attachment' => $file] : []
        );
        $request->setRouteResolver(fn () => Route::getRoutes()->getByName('public.request.store'));

        return Validator::make(
            $request->all(),
            ['payment_attachment' => $request->rules()['payment_attachment']]
        );
    }

    private function attachmentsValidator(array $files)
    {
        $request = StoreFacilitiesUtilizationRequest::create(
            '/request-facility',
            'POST',
            [],
            [],
            ['attachments' => $files]
        );
        $request->setRouteResolver(fn () => Route::getRoutes()->getByName('public.request.store'));
        $rules = $request->rules();

        return Validator::make($request->all(), [
            'attachments' => $rules['attachments'],
            'attachments.*' => $rules['attachments.*'],
        ]);
    }
}
