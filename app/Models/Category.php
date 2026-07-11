<?php

/**
 * Model Category.
 *
 * Menangani kategori tiket (Hardware, Software, Jaringan, dll).
 */
class Category extends Model
{
    /**
     * Mengambil semua kategori, dipakai di form buat/edit tiket
     * dan dropdown filter.
     *
     * @return array
     */
    public function all()
    {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mencari kategori berdasarkan ID.
     *
     * @param int $id
     * @return array|false
     */
    public function find($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Membuat kategori baru.
     *
     * @param string $name
     * @param string $description
     * @return bool
     */
    public function create($name, $description)
    {
        $stmt = $this->db->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        return $stmt->execute([$name, $description]);
    }

    /**
     * Memperbarui kategori.
     *
     * @param int $id
     * @param string $name
     * @param string $description
     * @return bool
     */
    public function update($id, $name, $description)
    {
        $stmt = $this->db->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?');
        return $stmt->execute([$name, $description, $id]);
    }

    /**
     * Menghapus kategori. Akan gagal (exception FK constraint) kalau
     * masih ada tiket yang memakai kategori ini — dicek dulu di controller.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Menghitung jumlah tiket yang memakai kategori ini,
     * dipakai untuk validasi sebelum hapus.
     *
     * @param int $id
     * @return int
     */
    public function countTickets($id)
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM tickets WHERE category_id = ?');
        $stmt->execute([$id]);
        return (int) $stmt->fetchColumn();
    }
}
