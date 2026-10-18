<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Zero-dependency CSV and Excel export. No PDF/Excel package is installed
 * in this project, so:
 *  - CSV is written natively via fputcsv() on a streamed response.
 *  - "Excel" is an HTML table served with an .xls extension and the legacy
 *    `application/vnd.ms-excel` mime type — Excel, Numbers, and Google
 *    Sheets all open this correctly; it's a well-established dependency-free
 *    technique and needs no PhpSpreadsheet/Maatwebsite package.
 *  - PDF is intentionally NOT generated here — see ReportController, which
 *    renders a print-optimized Blade view instead (browser "Save as PDF").
 */
class ReportExportService
{
    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $this->withExtension($filename, 'csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @param  array<int, string>  $headers
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public function excel(string $filename, string $title, array $headers, iterable $rows): Response
    {
        $html = view('admin.reports.exports.excel', compact('title', 'headers', 'rows'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$this->withExtension($filename, 'xls').'"',
        ]);
    }

    private function withExtension(string $filename, string $extension): string
    {
        return str_ends_with($filename, ".{$extension}") ? $filename : "{$filename}.{$extension}";
    }
}
