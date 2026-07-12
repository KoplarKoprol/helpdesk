<?php

/**
 * Model User.
 *
 * Menangani query terkait akun pengguna: autentikasi, daftar user,
 * pendaftaran akun baru, dan perubahan role (RBAC).
 */
class User extends Model
{
    /**
     * Mencari user berdasarkan email, dipakai untuk login.
     *
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mencari user berdasarkan ID.
     *
     * @param int $id
     * @return array|false
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil semua user beserta nama role-nya, untuk dashboard admin.
     *
     * @return array
     */
    public function all()
    {
        $stmt = $this->db->query(
            'SELECT users.id, users.name, users.email, roles.name AS role, roles.id AS role_id
             FROM users JOIN roles ON users.role_id = roles.id
             ORDER BY users.name'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Membuat akun user baru. Password otomatis di-hash dengan bcrypt.
     *
     * @param string $name
     * @param string $email
     * @param string $password Password polos (belum di-hash)
     * @param int $roleId
     * @return bool
     */
    public function create($name, $email, $password, $roleId)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)'
        );
        return $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $roleId]);
    }

    /**
     * Mengubah role user. Dipakai admin dari dashboard untuk
     * menaikkan/menurunkan hak akses user (misal user -> teknisi).
     *
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function updateRole($userId, $roleId)
    {
        $stmt = $this->db->prepare('UPDATE users SET role_id = ? WHERE id = ?');
        return $stmt->execute([$roleId, $userId]);
    }

    /**
     * Mengambil daftar user dengan role teknisi, untuk dropdown assign tiket.
     *
     * @return array
     */
    public function getTeknisiList()
    {
        $stmt = $this->db->prepare(
            'SELECT users.id, users.name
             FROM users JOIN roles ON users.role_id = roles.id
             WHERE roles.name = ?
             ORDER BY users.name'
        );
        $stmt->execute(['teknisi']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Menghapus user. Dicek dulu di controller apakah user ini
     * masih punya tiket terkait (sebagai pembuat atau teknisi),
     * supaya data tiket tidak kehilangan relasinya.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Menghitung jumlah tiket yang terkait dengan user ini,
     * baik sebagai pembuat maupun sebagai teknisi yang ditugaskan.
     *
     * @param int $id
     * @return int
     */
    public function countRelatedTickets($id)
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM tickets WHERE user_id = ? OR teknisi_id = ?'
        );
        $stmt->execute([$id, $id]);
        return (int) $stmt->fetchColumn();
    }
}
