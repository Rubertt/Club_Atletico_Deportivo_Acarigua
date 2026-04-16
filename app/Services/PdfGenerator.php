<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Genera PDFs con TCPDF cuando está instalado (composer require tecnickcom/tcpdf).
 * Si TCPDF no está disponible, devuelve un HTML formateado listo para imprimir
 * (Ctrl+P → "Guardar como PDF"), para no bloquear el flujo en entornos sin Composer.
 */
final class PdfGenerator
{
    public function available(): bool
    {
        return class_exists(\TCPDF::class);
    }

    /**
     * @return array{mime:string, filename:string, content:string}
     */
    public function render(string $title, string $html, string $filename): array
    {
        if ($this->available()) {
            return $this->renderWithTcpdf($title, $html, $filename);
        }
        return $this->renderAsHtml($title, $html, $filename);
    }

    private function renderWithTcpdf(string $title, string $html, string $filename): array
    {
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('CADA');
        $pdf->SetAuthor('Club Atlético Deportivo Acarigua');
        $pdf->SetTitle($title);
        $pdf->SetMargins(12, 14, 12);
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterData([0, 0, 0], [0, 0, 0]);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML($html, true, false, true, false, '');

        $content = $pdf->Output('', 'S');
        return ['mime' => 'application/pdf', 'filename' => $filename . '.pdf', 'content' => $content];
    }

    private function renderAsHtml(string $title, string $html, string $filename): array
    {
        $full = '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>' . htmlspecialchars($title) . '</title>
        <style>
            @media print { body { margin: 14mm; } .no-print { display: none; } }
            body { font-family: -apple-system, Segoe UI, Roboto, sans-serif; color: #1F2937; max-width: 210mm; margin: 0 auto; padding: 20px; }
            h1, h2, h3 { color: #DC2626; }
            table { width: 100%; border-collapse: collapse; margin: 12px 0; }
            th, td { border: 1px solid #E5E7EB; padding: 8px; text-align: left; font-size: 13px; }
            th { background: #F9FAFB; }
            .header { border-bottom: 3px solid #DC2626; padding-bottom: 10px; margin-bottom: 20px; }
            .header-title { font-size: 22px; font-weight: 700; margin: 0; }
            .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
            .avatar { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
            .badge { display:inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; background: #FEE2E2; color: #991B1B; font-weight:600; }
            .no-print { background: #DC2626; color: #fff; border: 0; padding: 8px 20px; border-radius: 6px; cursor: pointer; margin-bottom: 16px; }
        </style></head><body>
        <button class="no-print" onclick="window.print()">🖨️ Imprimir / Guardar como PDF</button>
        ' . $html . '
        </body></html>';
        return ['mime' => 'text/html; charset=utf-8', 'filename' => $filename . '.html', 'content' => $full];
    }
}
