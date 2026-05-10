</main>

<!-- FOOTER -->
<footer class="footer">
    <p>© <?= date('Y') ?> Kontraktor Management System &mdash; Built with PHP Native MVC</p>
</footer>

<!-- Alert / Toast notification -->
<?php if (!empty($_SESSION['flash'])): ?>
<div class="toast" id="toast">
    <span class="toast-icon"><?= $_SESSION['flash']['type'] === 'success' ? '✓' : '✕' ?></span>
    <?= htmlspecialchars($_SESSION['flash']['message']) ?>
</div>
<script>
    setTimeout(() => {
        const t = document.getElementById('toast');
        if(t) { t.classList.add('show'); setTimeout(() => t.classList.remove('show'), 3000); }
    }, 100);
</script>
<?php unset($_SESSION['flash']); endif; ?>

<script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
</body>
</html>
