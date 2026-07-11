<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah User</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <h2>Tambah User Baru</h2>
    <form action="<?= url('/admin/users') ?>" method="POST">
        <?= csrfField() ?>
        <p>
            <label>Nama</label><br>
            <input type="text" name="name" required>
        </p>
        <p>
            <label>Email</label><br>
            <input type="email" name="email" required>
        </p>
        <p>
            <label>Password</label><br>
            <input type="password" name="password" required>
        </p>
        <p>
            <label>Role</label><br>
            <select name="role_id" required>
                <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <button type="submit">Simpan</button>
    </form>
    <p><a href="<?= url('/admin/dashboard') ?>">Kembali ke Dashboard</a></p>
</body>
</html>
