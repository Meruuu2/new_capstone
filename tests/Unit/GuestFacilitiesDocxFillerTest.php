<?php

namespace Tests\Unit;

use App\Http\Controllers\Guest\FacilitiesFormPdfController;
use App\Services\GuestFacilitiesDocxFiller;
use DOMDocument;
use DOMXPath;
use ReflectionMethod;
use ZipArchive;
use Tests\TestCase;

class GuestFacilitiesDocxFillerTest extends TestCase
{
    public function test_guest_values_are_written_into_the_supplied_form_template(): void
    {
        $templatePath = base_path('FORMS/GUEST-FACILITIES-AND-UTILIZATION-FROM-TEMPLATE.docx');
        $this->assertFileExists($templatePath);
        $docxPath = tempnam(sys_get_temp_dir(), 'guest_form_test_');
        copy($templatePath, $docxPath);

        try {
            app(GuestFacilitiesDocxFiller::class)->fill($docxPath, $this->formData());
            $documentText = $this->documentText($docxPath);

            $this->assertStringContainsString('Name: Alex Guest', $documentText);
            $this->assertStringContainsString('Contact No.: 0912-345-6789', $documentText);
            $this->assertStringContainsString('Facility: Busalian Hall', $documentText);
            $this->assertStringContainsString('Email: alex@example.com', $documentText);
            $this->assertStringContainsString('Purpose: Community event', $documentText);
            $this->assertStringContainsString('Immediate Head Riley Head', $documentText);
            $this->assertStringContainsString('₱ 10,000.00', $documentText);
        } finally {
            @unlink($docxPath);
        }
    }

    public function test_filled_guest_template_can_be_returned_as_a_pdf(): void
    {
        $templatePath = base_path('FORMS/GUEST-FACILITIES-AND-UTILIZATION-FROM-TEMPLATE.docx');
        $docxPath = tempnam(sys_get_temp_dir(), 'guest_form_test_');
        copy($templatePath, $docxPath);
        app(GuestFacilitiesDocxFiller::class)->fill($docxPath, $this->formData());

        $method = new ReflectionMethod(FacilitiesFormPdfController::class, 'convertAndDownload');
        $response = $method->invoke(app(FacilitiesFormPdfController::class), $docxPath, 'Guest-Form-Test');
        $pdfPath = $response->getFile()->getPathname();

        try {
            $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF-', file_get_contents($pdfPath));
        } finally {
            @unlink($pdfPath);
            @unlink($docxPath);
        }
    }

    private function formData(): array
    {
        return [
            'control_no' => 'FM-TEST-00001',
            'date_request' => '2026-10-24',
            'client_name' => 'Alex Guest',
            'contact_number' => '0912-345-6789',
            'email' => 'alex@example.com',
            'address' => 'Sibalom, Antique',
            'organization' => 'Community Group',
            'facility_names' => ['Busalian Hall'],
            'date_activity' => '2026-12-01',
            'date_end' => '2026-12-01',
            'time_range' => ['start' => '09:00', 'end' => '12:00'],
            'purpose' => 'Community event',
            'equipment' => ['monobloc_chair' => 20],
            'noted_name' => 'Riley Head',
            'facility_rows' => [[
                'name' => 'Busalian Hall',
                'quantity' => '1',
                'rate' => 10000,
                'days' => '1',
                'hours' => '3',
                'total' => 10000,
            ]],
            'rental_total' => 10000,
        ];
    }

    private function documentText(string $docxPath): string
    {
        $archive = new ZipArchive();
        $this->assertTrue($archive->open($docxPath) === true);
        $xml = $archive->getFromName('word/document.xml');
        $archive->close();

        $document = new DOMDocument();
        $document->loadXML($xml, LIBXML_NONET);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        return implode('', array_map(
            fn ($node) => $node->nodeValue,
            iterator_to_array($xpath->query('//w:t'))
        ));
    }
}