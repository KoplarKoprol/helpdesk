<?php

/**
 * Model Comment.
 *
 * Riwayat komunikasi/komentar pada sebuah tiket, bisa diisi
 * oleh pembuat tiket, teknisi, maupun admin.
 */
class Comment extends Model
{
    /**
     * Mengambil semua komentar pada satu tiket, urut dari yang terlama.
     *
     * @param int $ticketId
     * @return array
     */
    public function getByTicket($ticketId)
    {
        $stmt = $this->db->prepare(
            'SELECT comments.*, users.name AS penulis
             FROM comments
             JOIN users ON comments.user_id = users.id
             WHERE ticket_id = ?
             ORDER BY comments.created_at ASC'
        );
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menambahkan komentar baru ke tiket.
     *
     * @param int $ticketId
     * @param int $userId Penulis komentar
     * @param string $message
     * @return bool
     */
    public function create($ticketId, $userId, $message)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO comments (ticket_id, user_id, message) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$ticketId, $userId, $message]);
    }

    /**
     * Mencari komentar berdasarkan ID, dipakai untuk validasi
     * kepemilikan sebelum edit/hapus.
     *
     * @param int $id
     * @return array|false
     */
    public function find($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM comments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Memperbarui isi komentar. Hanya penulis komentar yang boleh mengedit
     * (divalidasi di controller).
     *
     * @param int $id
     * @param string $message
     * @return bool
     */
    public function update($id, $message)
    {
        $stmt = $this->db->prepare('UPDATE comments SET message = ? WHERE id = ?');
        return $stmt->execute([$message, $id]);
    }

    /**
     * Menghapus komentar.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM comments WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
