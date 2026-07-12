<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <h2>Daftar Akun Baru</h2>
    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form action="<?= url('/register') ?>" method="POST">
        <?= csrfField() ?>
        <p>
            <input type="text" name="name" placeholder="Nama Lengkap" required>
        </p>
        <p>
            <input type="email" name="email" placeholder="Email" required>
        </p>
        <p>
            <input type="password" name="password" placeholder="Password (min. 6 karakter)" required>
        </p>
        <p>
            <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
        </p>
        <button type="submit">Daftar</button>
    </form>
    <p>Sudah punya akun? <a href="<?= url('/login') ?>">Login di sini</a></p>
</body>
</html>
