<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php if (!empty($_SESSION['flash'])): ?>
<div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?>"><?= htmlspecialchars($_SESSION['flash']['message']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><span class="card-title">Tambah Pengguna</span></div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/public/index.php?page=pengguna&action=store">
            <div class="form-grid">
                <div class="form-group"><label>Nama</label><input type="text" name="nama" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" minlength="6" required></div>
                <div class="form-group"><label>Role</label><select name="role" required><option value="user">Pekerja</option><option value="manager">Manager</option><option value="owner">Owner</option><option value="admin">Administrator</option></select></div>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Pengguna</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><span class="card-title">Daftar Pengguna</span></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['nama']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars(getRoleLabel($user['role'])) ?></td>
                    <td><?= htmlspecialchars($user['status']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                    <td>
                        <a class="btn btn-secondary btn-sm" href="<?= BASE_URL ?>/public/index.php?page=pengguna&action=edit&id=<?= (int)$user['id'] ?>">Edit</a>
                        <?php if ((int)$user['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                        <a class="btn btn-danger btn-sm" href="<?= BASE_URL ?>/public/index.php?page=pengguna&action=delete&id=<?= (int)$user['id'] ?>" onclick="return confirm('Hapus pengguna ini?')">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
