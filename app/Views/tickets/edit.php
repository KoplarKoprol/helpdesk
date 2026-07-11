<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Tiket</title>
</head>
<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <h2>Edit Tiket</h2>
    <form action="<?= url('/tickets/' . $ticket['id'] . '/update') ?>" method="POST">
        <?= csrfField() ?>
        <p>
            <label>Judul</label><br>
            <input type="text" name="title" value="<?= htmlspecialchars($ticket['title']) ?>" required>
        </p>
        <p>
            <label>Kategori</label><br>
            <select name="category_id" required>
                <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>" <?= $category['id'] == $ticket['category_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label>Nomor Telepon yang Bisa Dihubungi</label><br>
            <input type="tel" name="contact_phone" value="<?= htmlspecialchars($ticket['contact_phone']) ?>" pattern="\+?[0-9]{9,15}" required>
        </p>
        <p>
            <label>Prioritas</label><br>
            <select name="priority">
                <option value="low" <?= $ticket['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
                <option value="medium" <?= $ticket['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                <option value="high" <?= $ticket['priority'] === 'high' ? 'selected' : '' ?>>High</option>
            </select>
        </p>
        <p>
            <label>Deskripsi</label><br>
            <textarea name="description" rows="5" required><?= htmlspecialchars($ticket['description']) ?></textarea>
        </p>
        <button type="submit">Simpan Perubahan</button>
    </form>

    
    </form>
</body>
</html>
