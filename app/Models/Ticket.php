<?php

/**
 * Model Ticket.
 *
 * Entitas inti aplikasi: CRUD tiket, pencarian dengan filter,
 * assign teknisi, dan perubahan status.
 */
class Ticket extends Model
{
    /**
     * Menghitung jumlah tiket untuk tiap status, dipakai untuk
     * kartu ringkasan di dashboard admin.
     *
     * @return array Contoh: ['open' => 5, 'in_progress' => 3, 'resolved' => 10, 'closed' => 2]
     */
    public function countByStatus()
    {
        $stmt = $this->db->query('SELECT status, COUNT(*) AS total FROM tickets GROUP BY status');
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'open' => (int) ($rows['open'] ?? 0),
            'in_progress' => (int) ($rows['in_progress'] ?? 0),
            'resolved' => (int) ($rows['resolved'] ?? 0),
            'closed' => (int) ($rows['closed'] ?? 0),
        ];
    }

    /**
     * Mencari tiket dengan filter opsional. Dipakai untuk daftar tiket
     * (per role) dan dashboard admin, sekaligus jadi dasar filter &
     * pencarian judul.
     *
     * @param array $filters Kunci yang didukung: user_id, teknisi_id,
     *                        status, category_id, keyword
     * @return array
     */
    public function search($filters = [])
    {
        $sql = 'SELECT tickets.*, users.name AS pembuat, categories.name AS kategori
                FROM tickets
                JOIN users ON tickets.user_id = users.id
                JOIN categories ON tickets.category_id = categories.id
                WHERE 1=1';
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= ' AND tickets.user_id = ?';
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['teknisi_id'])) {
            $sql .= ' AND tickets.teknisi_id = ?';
            $params[] = $filters['teknisi_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND tickets.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= ' AND tickets.category_id = ?';
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['keyword'])) {
            $sql .= ' AND tickets.title LIKE ?';
            $params[] = '%' . $filters['keyword'] . '%';
        }

        $sql .= ' ORDER BY tickets.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Membuat tiket baru.
     *
     * @param int $userId ID pembuat tiket
     * @param int $categoryId
     * @param string $title
     * @param string $description
     * @param string $contactPhone Nomor telepon yang bisa dihubungi terkait tiket ini
     * @param string $priority low|medium|high
     * @return string ID tiket yang baru dibuat
     */
    public function create($userId, $categoryId, $title, $description, $contactPhone, $priority)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO tickets (user_id, category_id, title, description, contact_phone, priority)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $categoryId, $title, $description, $contactPhone, $priority]);
        return $this->db->lastInsertId();
    }

    /**
     * Menugaskan teknisi ke tiket. Status otomatis berubah jadi in_progress.
     *
     * @param int $ticketId
     * @param int $teknisiId
     * @return bool
     */
    public function assignTeknisi($ticketId, $teknisiId)
    {
        $stmt = $this->db->prepare('UPDATE tickets SET teknisi_id = ?, status = ? WHERE id = ?');
        return $stmt->execute([$teknisiId, 'in_progress', $ticketId]);
    }

    /**
     * Mengambil detail 1 tiket lengkap dengan nama pembuat,
     * kategori, dan teknisi (kalau ada).
     *
     * @param int $id
     * @return array|false
     */
    public function find($id)
    {
        $stmt = $this->db->prepare(
            'SELECT tickets.*, users.name AS pembuat, categories.name AS kategori, teknisi.name AS teknisi
             FROM tickets
             JOIN users ON tickets.user_id = users.id
             JOIN categories ON tickets.category_id = categories.id
             LEFT JOIN users AS teknisi ON tickets.teknisi_id = teknisi.id
             WHERE tickets.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Memperbarui data tiket (dipakai user pemilik tiket).
     *
     * @param int $id
     * @param string $title
     * @param string $description
     * @param int $categoryId
     * @param string $contactPhone
     * @param string $priority
     * @return bool
     */
    public function update($id, $title, $description, $categoryId, $contactPhone, $priority)
    {
        $stmt = $this->db->prepare(
            'UPDATE tickets SET title = ?, description = ?, category_id = ?, contact_phone = ?, priority = ? WHERE id = ?'
        );
        return $stmt->execute([$title, $description, $categoryId, $contactPhone, $priority, $id]);
    }

    /**
     * Mengubah status tiket (dipakai teknisi/admin).
     *
     * @param int $id
     * @param string $status open|in_progress|resolved|closed
     * @return bool
     */
    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare('UPDATE tickets SET status = ? WHERE id = ?');
        return $stmt->execute([$status, $id]);
    }

    /**
     * Menghapus tiket (dipakai user pemilik tiket).
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM tickets WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
