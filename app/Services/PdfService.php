<?php

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * PdfService.
 *
 * Mencetak detail satu tiket beserta riwayat komentar ke file PDF,
 * memakai library Dompdf. isRemoteEnabled sengaja dimatikan agar
 * Dompdf tidak bisa memuat resource dari URL eksternal (mitigasi SSRF).
 */
class PdfService
{
    /**
     * Merender tiket ke PDF dan menyimpannya ke path tujuan.
     *
     * @param array $ticket Data tiket, hasil dari Ticket::find()
     * @param array $comments Riwayat komentar tiket
     * @param string $filePath Path lengkap file .pdf yang akan dibuat
     */
    public function ticketToPdf(array $ticket, array $comments, string $filePath)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $html = $this->renderHtml($ticket, $comments);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($filePath, $dompdf->output());
    }

    /**
     * Merender template HTML tiket jadi string, untuk di-load Dompdf.
     *
     * @param array $ticket
     * @param array $comments
     * @return string
     */
    private function renderHtml(array $ticket, array $comments): string
    {
        ob_start();
        require __DIR__ . '/../Views/tickets/pdf.php';
        return ob_get_clean();
    }
}
