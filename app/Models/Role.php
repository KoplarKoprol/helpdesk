<?php

/**
 * Model Role.
 *
 * Menangani query untuk 3 role RBAC: admin, teknisi, user.
 */
class Role extends Model
{
    /**
     * Mengambil semua role, dipakai untuk dropdown ubah role.
     *
     * @return array
     */
    public function all()
    {
        $stmt = $this->db->query('SELECT * FROM roles ORDER BY id');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mencari role berdasarkan nama, dipakai saat register
     * untuk menentukan role default ('user').
     *
     * @param string $name
     * @return array|false
     */
    public function findByName($name)
    {
        $stmt = $this->db->prepare('SELECT * FROM roles WHERE name = ?');
        $stmt->execute([$name]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
