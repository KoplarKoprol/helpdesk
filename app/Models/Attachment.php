<?php

/**
 * Model Attachment.
 *
 * Lampiran file yang diupload user saat membuat tiket.
 */
class Attachment extends Model
{
    /**
     * Mengambil semua lampiran milik satu tiket.
     *
     * @param int $ticketId
     * @return array
     */
    public function getByTicket($ticketId)
    {
        $stmt = $this->db->prepare('SELECT * FROM attachments WHERE ticket_id = ?');
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menyimpan metadata lampiran setelah file berhasil diupload ke server.
     *
     * @param int $ticketId
     * @param string $filePath Path relatif file (contoh: /uploads/xxx.pdf)
     * @param string $fileType Ekstensi file
     * @param int $fileSize Ukuran file dalam byte
     * @return bool
     */
    public function create($ticketId, $filePath, $fileType, $fileSize)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO attachments (ticket_id, file_path, file_type, file_size) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$ticketId, $filePath, $fileType, $fileSize]);
    }

    /**
     * Mencari lampiran berdasarkan ID.
     *
     * @param int $id
     * @return array|false
     */
    public function find($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM attachments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Menghapus metadata lampiran dari database. File fisiknya
     * dihapus terpisah di controller.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM attachments WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
