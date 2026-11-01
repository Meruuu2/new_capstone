<?php

namespace App\Http\Controllers\Traits;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait ConvertsToPdf
{
    protected function convertAndDownload(string $docxPath, string $baseName): BinaryFileResponse
    {
        $outDir  = sys_get_temp_dir();
        $pdfPath = $outDir . '/' . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        $cmd = sprintf(
            'soffice --headless --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($outDir),
            escapeshellarg($docxPath)
        );
        exec($cmd, $output, $exitCode);

        @unlink($docxPath);

        if ($exitCode !== 0 || !file_exists($pdfPath)) {
            return response()
                ->download($docxPath, $baseName . '.docx')
                ->deleteFileAfterSend(true);
        }

        return response()
            ->download($pdfPath, $baseName . '.pdf', ['Content-Type' => 'application/pdf'])
            ->deleteFileAfterSend(true);
    }
}
