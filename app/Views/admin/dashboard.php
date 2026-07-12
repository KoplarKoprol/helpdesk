<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="<?= url('/css/style.css') ?>">
</head>
<body>
    <?php include __DIR__ . '/../partials/flash.php'; ?>

    <h2>Ringkasan Tiket</h2>
    <div style="display: flex; gap: 12px; margin-bottom: 20px;">
        <div style="flex: 1; padding: 16px; border: 1px solid #ccc; border-radius: 6px; background: #fff3cd; text-align: center;">
            <div style="font-size: 24px; font-weight: bold;"><?= $summary['open'] ?></div>
            <div>Open</div>
        </div>
        <div style="flex: 1; padding: 16px; border: 1px solid #ccc; border-radius: 6px; background: #cfe2ff; text-align: center;">
            <div style="font-size: 24px; font-weight: bold;"><?= $summary['in_progress'] ?></div>
            <div>In Progress</div>
        </div>
        <div style="flex: 1; padding: 16px; border: 1px solid #ccc; border-radius: 6px; background: #d1e7dd; text-align: center;">
            <div style="font-size: 24px; font-weight: bold;"><?= $summary['resolved'] ?></div>
            <div>Resolved</div>
        </div>
        <div style="flex: 1; padding: 16px; border: 1px solid #ccc; border-radius: 6px; background: #e2e3e5; text-align: center;">
            <div style="font-size: 24px; font-weight: bold;"><?= $summary['closed'] ?></div>
            <div>Closed</div>
        </div>
    </div>

    <h2>Manajemen User</h2>
    <p>
        <a href="<?= url('/admin/users/create') ?>">+ Tambah User</a> |
        <a href="<?= url('/categories') ?>">Kelola Kategori</a> |
        <a href="<?= url('/reports/export-excel') ?>">Export Laporan Tiket ke Excel</a> |
        <a href="<?= url('/logout') ?>">Logout</a>
    </p>
    <table border="1" cellpadding="8">
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Role Saat Ini</th>
            <th>Ubah Role</th>
            <th>Aksi</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['name']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td>
                <form action="<?= url('/admin/users/update-role') ?>" method="POST">
        <?= csrfField() ?>
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <select name="role_id">
                        <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= $role['id'] == $user['role_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Simpan</button>
                </form>
            </td>
            <td>
                <form action="<?= url('/admin/users/' . $user['id'] . '/delete') ?>" method="POST" onsubmit="return confirm('Hapus user ini?')">
        <?= csrfField() ?>
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Daftar Tiket</h2>

    <form action="<?= url('/admin/dashboard') ?>" method="GET" style="margin-bottom: 12px;">
        <input type="text" name="keyword" placeholder="Cari judul tiket..." value="<?= htmlspecialchars($filters['keyword']) ?>">
        <select name="status">
            <option value="">Semua Status</option>
            <option value="open" <?= $filters['status'] === 'open' ? 'selected' : '' ?>>Open</option>
            <option value="in_progress" <?= $filters['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
            <option value="resolved" <?= $filters['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
            <option value="closed" <?= $filters['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
        </select>
        <select name="category_id">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $category): ?>
            <option value="<?= $category['id'] ?>" <?= $filters['category_id'] == $category['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($category['name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filter</button>
        <a href="<?= url('/admin/dashboard') ?>">Reset</a>
    </form>

    <table border="1" cellpadding="8">
        <tr>
            <th>Judul</th>
            <th>Pembuat</th>
            <th>Kategori</th>
            <th>Prioritas</th>
            <th>Status Saat Ini</th>
            <th>Ubah Status</th>
            <th>Detail</th>
        </tr>
        <?php foreach ($tickets as $ticket): ?>
        <tr <?= $ticket['priority'] === 'high' ? 'style="background-color: #f8d7da;"' : '' ?>>
            <td><?= htmlspecialchars($ticket['title']) ?></td>
            <td><?= htmlspecialchars($ticket['pembuat']) ?></td>
            <td><?= htmlspecialchars($ticket['kategori']) ?></td>
            <td><?= htmlspecialchars($ticket['priority']) ?></td>
            <td><?= htmlspecialchars($ticket['status']) ?></td>
            <td>
                <form action="<?= url('/tickets/' . $ticket['id'] . '/status') ?>" method="POST">
        <?= csrfField() ?>
                    <select name="status">
                        <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="resolved" <?= $ticket['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                        <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                    </select>
                    <button type="submit">Simpan</button>
                </form>
            </td>
            <td><a href="<?= url('/tickets/' . $ticket['id']) ?>">Lihat</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($tickets)): ?>
        <tr><td colspan="7">Belum ada tiket.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
