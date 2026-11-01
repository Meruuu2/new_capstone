<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class GuestFacilitiesDocxFiller
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function fill(string $docxPath, array $data): void
    {
        $archive = new ZipArchive();
        if ($archive->open($docxPath) !== true) {
            throw new RuntimeException('Could not open the guest DOCX template.');
        }

        $xml = $archive->getFromName('word/document.xml');
        if ($xml === false) {
            $archive->close();
            throw new RuntimeException('The guest DOCX is missing its main document.');
        }

        $document = new DOMDocument();
        $document->preserveWhiteSpace = true;
        if (! $document->loadXML($xml, LIBXML_NONET)) {
            $archive->close();
            throw new RuntimeException('The guest DOCX document XML is invalid.');
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', self::WORD_NS);
        $body = $xpath->query('//w:body')->item(0);
        $table = $body ? $xpath->query('./w:tbl', $body)->item(0) : null;
        if (! $body || ! $table) {
            $archive->close();
            throw new RuntimeException('The guest DOCX form layout was not found.');
        }

        $this->appendAfterLabel($document, $xpath->query('./w:p', $body), 'Ctrl. No.:', (string) $data['control_no']);
        $this->appendAfterLabel($document, $xpath->query('./w:p', $body), 'Date of Application:', $this->formatDate($data['date_request']));

        $rows = iterator_to_array($xpath->query('./w:tr', $table));
        $fieldRows = array_slice($rows, 0, 5);
        $this->appendCellLabel($document, $xpath, $fieldRows[0], 0, 'Name:', (string) $data['client_name']);
        $this->appendCellLabel($document, $xpath, $fieldRows[0], 1, 'Contact No.:', (string) $data['contact_number']);
        $this->appendCellLabel($document, $xpath, $fieldRows[1], 0, 'Address:', (string) $data['address']);
        if (! empty($data['email'])) {
            $addressCell = $xpath->query('./w:tc', $fieldRows[1])->item(0);
            $this->appendCellText($document, $xpath, $addressCell, 'Email: ' . $data['email']);
        }
        $this->appendCellLabel($document, $xpath, $fieldRows[2], 0, 'Organization/Agency:', (string) $data['organization']);
        $this->appendCellLabel($document, $xpath, $fieldRows[3], 0, 'Facility:', implode(', ', $data['facility_names']));

        $activityDate = $this->formatDate($data['date_activity']);
        if ($data['date_end'] && $data['date_end'] !== $data['date_activity']) {
            $activityDate .= ' to ' . $this->formatDate($data['date_end']);
        }
        $this->appendCellLabel($document, $xpath, $fieldRows[3], 1, 'Inclusive Date/s of Use:', $activityDate);
        $this->appendCellLabel($document, $xpath, $fieldRows[3], 2, 'Time:', $this->formatTimeRange($data['time_range']));

        $purpose = trim((string) $data['purpose']);
        $equipment = $this->formatEquipment($data['equipment']);
        if ($equipment !== '') {
            $purpose .= ' | Equipment requested: ' . $equipment;
        }
        $this->appendCellLabel($document, $xpath, $fieldRows[4], 0, 'Purpose:', $purpose);

        $headerIndex = null;
        $totalIndex = null;
        foreach ($rows as $index => $row) {
            $firstCell = $xpath->query('./w:tc[1]', $row)->item(0);
            $text = $firstCell ? $this->nodeText($xpath, $firstCell) : '';
            if ($headerIndex === null && trim($text) === 'Facility') {
                $headerIndex = $index;
            }
            if (trim($text) === 'TOTAL') {
                $totalIndex = $index;
                break;
            }
        }
        if ($headerIndex === null || $totalIndex === null || $totalIndex <= $headerIndex) {
            $archive->close();
            throw new RuntimeException('The guest DOCX rental table was not found.');
        }

        $totalRow = $rows[$totalIndex];
        $venueRows = array_slice($rows, $headerIndex + 1, $totalIndex - $headerIndex - 1);
        while (count($venueRows) < count($data['facility_rows'])) {
            $newRow = $venueRows[count($venueRows) - 1]->cloneNode(true);
            $table->insertBefore($newRow, $totalRow);
            $venueRows[] = $newRow;
        }

        foreach ($data['facility_rows'] as $index => $venue) {
            $cells = $xpath->query('./w:tc', $venueRows[$index]);
            $values = [
                $venue['name'],
                $venue['quantity'],
                $venue['rate'] === null ? 'GSU rate' : '₱ ' . number_format($venue['rate'], 2),
                $venue['days'],
                $venue['hours'],
                $venue['total'] === null ? '—' : '₱ ' . number_format($venue['total'], 2),
            ];
            foreach ($values as $cellIndex => $value) {
                $cell = $cells->item($cellIndex);
                if ($cell) {
                    $this->appendCellText($document, $xpath, $cell, (string) $value);
                }
            }
        }

        $totalCurrencyCell = $xpath->query('./w:tc[2]', $totalRow)->item(0);
        if ($totalCurrencyCell) {
            $this->appendAfterLabel($document, $xpath->query('.//w:p', $totalCurrencyCell), '₱', number_format($data['rental_total'], 2));
        }

        foreach ($xpath->query('.//w:tc', $table) as $cell) {
            $text = $this->nodeText($xpath, $cell);
            if (str_contains($text, 'Client/Representative Printed Name')) {
                $this->appendAfterLabel(
                    $document,
                    $xpath->query('.//w:p', $cell),
                    'Client/Representative Printed Name & Signature',
                    (string) $data['client_name']
                );
            } elseif (str_contains($text, 'Noted:') && str_contains($text, 'Immediate Head')) {
                $this->appendAfterLabel($document, $xpath->query('.//w:p', $cell), 'Immediate Head', (string) $data['noted_name']);
            }
        }

        $archive->addFromString('word/document.xml', $document->saveXML());
        $archive->close();
    }

    private function appendCellLabel(DOMDocument $document, DOMXPath $xpath, DOMElement $row, int $cellIndex, string $label, string $value): void
    {
        $cell = $xpath->query('./w:tc', $row)->item($cellIndex);
        if (! $cell) {
            throw new RuntimeException('A labeled field cell is missing from the guest DOCX.');
        }
        $this->appendAfterLabel($document, $xpath->query('.//w:p', $cell), $label, $value);
    }

    private function appendAfterLabel(DOMDocument $document, iterable $paragraphs, string $label, string $value): void
    {
        if ($value === '') {
            return;
        }
        foreach ($paragraphs as $paragraph) {
            foreach ($paragraph->getElementsByTagNameNS(self::WORD_NS, 't') as $textNode) {
                if (str_contains($textNode->nodeValue, $label)) {
                    $run = $textNode->parentNode;
                    $newRun = $document->createElementNS(self::WORD_NS, 'w:r');
                    $runProperties = $run->getElementsByTagNameNS(self::WORD_NS, 'rPr')->item(0);
                    if ($runProperties) {
                        $newRun->appendChild($runProperties->cloneNode(true));
                    }
                    $newText = $document->createElementNS(self::WORD_NS, 'w:t');
                    $newText->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
                    $runText = implode('', array_map(
                        fn ($node) => $node->nodeValue,
                        iterator_to_array($run->getElementsByTagNameNS(self::WORD_NS, 't'))
                    ));
                    $space = preg_match('/\s$/u', $runText) ? '' : ' ';
                    $newText->appendChild($document->createTextNode($space . $value));
                    $newRun->appendChild($newText);
                    $run->parentNode->insertBefore($newRun, $run->nextSibling);
                    return;
                }
            }
        }
        throw new RuntimeException('Could not find the guest DOCX field label: ' . $label);
    }

    private function appendCellText(DOMDocument $document, DOMXPath $xpath, DOMElement $cell, string $value): void
    {
        if (trim($value) === '') {
            return;
        }
        $paragraph = $xpath->query('./w:p', $cell)->item(0);
        if (! $paragraph) {
            $paragraph = $document->createElementNS(self::WORD_NS, 'w:p');
            $cell->appendChild($paragraph);
        }
        $run = $document->createElementNS(self::WORD_NS, 'w:r');
        $text = $document->createElementNS(self::WORD_NS, 'w:t');
        $text->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        $text->appendChild($document->createTextNode(' ' . $value));
        $run->appendChild($text);
        $paragraph->appendChild($run);
    }

    private function nodeText(DOMXPath $xpath, DOMElement $element): string
    {
        return implode('', array_map(
            fn ($node) => $node->nodeValue,
            iterator_to_array($xpath->query('.//w:t', $element))
        ));
    }

    private function formatDate(?string $date): string
    {
        return $date ? date('F d, Y', strtotime($date)) : '';
    }

    private function formatTimeRange(array $range): string
    {
        if (empty($range['start']) || empty($range['end'])) {
            return '';
        }

        return date('g:i A', strtotime($range['start'])) . ' - ' . date('g:i A', strtotime($range['end']));
    }

    private function formatEquipment(array $equipment): string
    {
        $labels = [
            'monobloc_chair' => 'Monobloc chairs',
            'table' => 'Tables',
            'electric_fan' => 'Electric fans',
            'rostrum' => 'Rostra',
            'flag' => 'Flags',
            'sound' => 'Sound systems',
            'led' => 'LED walls',
        ];
        $items = [];
        foreach ($labels as $key => $label) {
            $quantity = (int) ($equipment[$key] ?? 0);
            if ($quantity > 0) {
                $items[] = $quantity . ' ' . $label;
            }
        }

        return implode(', ', $items);
    }
}