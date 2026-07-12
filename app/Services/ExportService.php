<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * ExportService.
 *
 * Membuat file Excel (.xlsx) berisi rekap seluruh tiket,
 * memakai library PhpSpreadsheet.
 */
class ExportService
{
    /**
     * Menulis data tiket ke file Excel dan menyimpannya ke path tujuan.
     *
     * @param array $tickets Data tiket, hasil dari Ticket::search()
     * @param string $filePath Path lengkap file .xlsx yang akan dibuat
     */
    public function exportTickets(array $tickets, string $filePath)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Tiket');

        $headers = ['No', 'Judul', 'Pembuat', 'Kontak', 'Kategori', 'Prioritas', 'Status', 'Tanggal Dibuat'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $row = 2;
        $no = 1;

        foreach ($tickets as $ticket) {
            $sheet->fromArray([
                $no++,
                $ticket['title'],
                $ticket['pembuat'],
                $ticket['contact_phone'],
                $ticket['kategori'],
                $ticket['priority'],
                $ticket['status'],
                $ticket['created_at'],
            ], null, 'A' . $row);
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}
