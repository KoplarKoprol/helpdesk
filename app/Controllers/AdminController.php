<?php

class AdminController extends Controller
{
    
    public function dashboard()
    {
        $this->requireRole(['admin']);

        $userModel = new User();
        $roleModel = new Role();
        $ticketModel = new Ticket();
        $categoryModel = new Category();

        $filters = [
            'status' => $_GET['status'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'keyword' => trim($_GET['keyword'] ?? ''),
        ];

        $this->view('admin/dashboard', [
            'users' => $userModel->all(),
            'roles' => $roleModel->all(),
            'tickets' => $ticketModel->search($filters),
            'categories' => $categoryModel->all(),
            'filters' => $filters,
            'summary' => $ticketModel->countByStatus(),
        ]);
    }


    public function createUser()
    {
        $this->requireRole(['admin']);

        $roleModel = new Role();
        $this->view('admin/create_user', ['roles' => $roleModel->all()]);
    }

    public function storeUser()
    {
        $this->requireRole(['admin']);

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId = (int) ($_POST['role_id'] ?? 0);

        if (!Validator::lengthBetween($name, 3, 100)) {
            $this->flash('Nama wajib diisi, 3-100 karakter.', 'error');
            $this->redirect('/admin/users/create');
        }

        if (!Validator::isEmail($email)) {
            $this->flash('Format email tidak valid.', 'error');
            $this->redirect('/admin/users/create');
        }

        if (!Validator::lengthBetween($password, 6, 72)) {
            $this->flash('Password harus 6-72 karakter.', 'error');
            $this->redirect('/admin/users/create');
        }

        $roleModel = new Role();
        $validRoleIds = array_column($roleModel->all(), 'id');

        if (!Validator::inList($roleId, $validRoleIds)) {
            $this->flash('Role tidak valid.', 'error');
            $this->redirect('/admin/users/create');
        }

        $userModel = new User();

        if ($userModel->findByEmail($email)) {
            $this->flash('Email sudah terdaftar.', 'error');
            $this->redirect('/admin/users/create');
        }

        $userModel->create($name, $email, $password, $roleId);
        $this->flash('User berhasil ditambahkan.');
        $this->redirect('/admin/dashboard');
    }

   
    public function updateUserRole()
    {
        $this->requireRole(['admin']);

        $userId = (int) ($_POST['user_id'] ?? 0);
        $roleId = (int) ($_POST['role_id'] ?? 0);

        $roleModel = new Role();
        $validRoleIds = array_column($roleModel->all(), 'id');

        if (!Validator::inList($roleId, $validRoleIds)) {
            $this->flash('Role tidak valid.', 'error');
            $this->redirect('/admin/dashboard');
        }

        $userModel = new User();

        if (!$userModel->findById($userId)) {
            $this->flash('User tidak ditemukan.', 'error');
            $this->redirect('/admin/dashboard');
        }

        $userModel->updateRole($userId, $roleId);
        $this->flash('Role user berhasil diubah.');
        $this->redirect('/admin/dashboard');
    }

    
    public function deleteUser($id)
    {
        $this->requireRole(['admin']);

        $userModel = new User();

        if ((int) $id === (int) $_SESSION['user']['id']) {
            $this->flash('Tidak bisa menghapus akun sendiri.', 'error');
            $this->redirect('/admin/dashboard');
        }

        if ($userModel->countRelatedTickets($id) > 0) {
            $this->flash('User tidak bisa dihapus karena masih memiliki tiket terkait.', 'error');
            $this->redirect('/admin/dashboard');
        }

        $userModel->delete($id);
        $this->flash('User berhasil dihapus.');
        $this->redirect('/admin/dashboard');
    }
}
