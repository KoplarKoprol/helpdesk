<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Kategori</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <h2>Tambah Kategori</h2>
    <form action="<?= url('/categories') ?>" method="POST">
        <?= csrfField() ?>
        <p>
            <label>Nama Kategori</label><br>
            <input type="text" name="name" required>
        </p>
        <p>
            <label>Deskripsi</label><br>
            <textarea name="description" rows="3"></textarea>
        </p>
        <button type="submit">Simpan</button>
    </form>
    <p><a href="<?= url('/categories') ?>">Kembali</a></p>
</body>
</html>
