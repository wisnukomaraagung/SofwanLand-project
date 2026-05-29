<?php require BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="card">
    <div class="card-body" style="padding: 24px; text-align: center;">
        <img
            src="<?= htmlspecialchars($imageUrl) ?>"
            alt="<?= htmlspecialchars($pageSubtitle) ?>"
            class="bukti-viewer-img"
            style="max-width: 100%; max-height: 75vh; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); border: 1px solid var(--border, #e8e8e8);"
        >
        <p class="text-muted" style="margin-top: 16px; font-size: 13px;">
            Klik kanan gambar untuk menyimpan atau membuka di tab baru.
        </p>
    </div>
</div>

<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
