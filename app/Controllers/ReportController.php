<?php

/**
 * ReportController.
 *
 * Menangani ekspor laporan: rekap semua tiket ke Excel (PhpSpreadsheet)
 * dan cetak detail satu tiket ke PDF (Dompdf).
 */
class ReportController extends Controller
{
    /**
     * Mengekspor semua tiket ke file .xlsx dan langsung men-trigger
     * download di browser. Hanya bisa diakses admin.
     */
    public function exportExcel()
    {
        $this->requireRole(['admin']);

        $ticketModel = new Ticket();
        $tickets = $ticketModel->search();

        $fileName = 'laporan_tiket_' . date('Y-m-d_His') . '.xlsx';
        $filePath = sys_get_temp_dir() . '/' . $fileName;

        $exportService = new ExportService();
        $exportService->exportTickets($tickets, $filePath);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        unlink($filePath);
        exit;
    }

    /**
     * Mengekspor detail satu tiket beserta riwayat komentar ke file .pdf.
     * Bisa diakses oleh role manapun yang berhak melihat tiket tersebut.
     *
     * @param int $id
     */
    public function exportPdf($id)
    {
        $this->requireRole(['user', 'teknisi', 'admin']);

        $ticketModel = new Ticket();
        $ticket = $ticketModel->find($id);

        if (!$ticket) {
            http_response_code(404);
            echo 'Tiket tidak ditemukan.';
            return;
        }

        $commentModel = new Comment();
        $comments = $commentModel->getByTicket($id);

        $fileName = 'tiket_' . $id . '.pdf';
        $filePath = sys_get_temp_dir() . '/' . $fileName;

        $pdfService = new PdfService();
        $pdfService->ticketToPdf($ticket, $comments, $filePath);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        unlink($filePath);
        exit;
    }
}
