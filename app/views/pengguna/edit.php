<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php if (!empty($_SESSION['flash'])): ?>
<div class="alert alert-<?= htmlspecialchars($_SESSION['flash']['type']) ?>"><?= htmlspecialchars($_SESSION['flash']['message']); unset($_SESSION['flash']); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><span class="card-title">Edit Pengguna</span></div>
    <div class="card-body">
        <form method="post" action="<?= BASE_URL ?>/public/index.php?page=pengguna&action=update&id=<?= (int)$user['id'] ?>">
            <div class="form-grid">
                <div class="form-group"><label>Nama</label><input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></div>
                <div class="form-group"><label>Password Baru</label><input type="password" name="password" minlength="6" placeholder="Kosongkan jika tidak diubah"></div>
                <div class="form-group"><label>Role</label><select name="role" required><?php foreach (['user' => 'Pekerja', 'manager' => 'Manager', 'owner' => 'Owner', 'admin' => 'Administrator'] as $value => $label): ?><option value="<?= $value ?>" <?= $user['role'] === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Status</label><select name="status" required><option value="aktif" <?= $user['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option><option value="nonaktif" <?= $user['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option></select></div>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= BASE_URL ?>/public/index.php?page=pengguna" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
