<?php
class AuthController extends Controller
{
    public function showLogin()
    {
        $this->view('auth/login');
    }

    public function login()
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!Validator::isEmail($email) || $password === '') {
            $this->view('auth/login', ['error' => 'Format email tidak valid atau password kosong.']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->view('auth/login', ['error' => 'Email atau password salah.']);
            return;
        }

        $roleModel = new Role();
        $roles = $roleModel->all();
        $roleName = 'user';
        foreach ($roles as $role) {
            if ($role['id'] == $user['role_id']) {
                $roleName = $role['name'];
            }
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $roleName,
        ];

        match ($roleName) {
            'admin' => $this->redirect('/admin/dashboard'),
            default => $this->redirect('/tickets'),
        };
    }

    public function showRegister()
    {
        $this->view('auth/register');
    }
    public function register()
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!Validator::lengthBetween($name, 3, 100)) {
            $this->view('auth/register', ['error' => 'Nama wajib diisi, 3-100 karakter.']);
            return;
        }

        if (!Validator::isEmail($email)) {
            $this->view('auth/register', ['error' => 'Format email tidak valid.']);
            return;
        }

        if ($password !== $confirmPassword) {
            $this->view('auth/register', ['error' => 'Konfirmasi password tidak cocok.']);
            return;
        }

        if (!Validator::lengthBetween($password, 6, 72)) {
            $this->view('auth/register', ['error' => 'Password harus 6-72 karakter.']);
            return;
        }

        $userModel = new User();

        if ($userModel->findByEmail($email)) {
            $this->view('auth/register', ['error' => 'Email sudah terdaftar.']);
            return;
        }

        $roleModel = new Role();
        $defaultRole = $roleModel->findByName('user');

        $userModel->create($name, $email, $password, $defaultRole['id']);

        $this->flash('Registrasi berhasil, silakan login.');
        $this->redirect('/login');
    }
    public function logout()
    {
        session_destroy();
        session_start();
        setFlash('Anda berhasil logout.');
        header('Location: ' . url('/login'));
        exit;
    }
}
