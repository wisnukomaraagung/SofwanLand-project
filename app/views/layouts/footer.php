</main>

<!-- FOOTER -->
<footer class="footer">
    <p>© <?= date('Y') ?> Kontraktor Management System &mdash; Built with PHP Native MVC</p>
</footer>

<!-- Alert / Toast notification (SweetAlert2) -->
<?php if (!empty($_SESSION['flash'])): ?>
<script>
    (function() {
        const flash = <?= json_encode($_SESSION['flash']) ?>;
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });

        Toast.fire({
            icon: flash.type === 'success' ? 'success' : (flash.type === 'error' ? 'error' : 'info'),
            title: flash.message
        });
    })();
</script>
<?php unset($_SESSION['flash']); endif; ?>
<?php if (!empty($_SESSION['error'])): ?>
<script>
    (function() {
        const msg = <?= json_encode($_SESSION['error']) ?>;
        Swal.fire({ icon: 'error', title: 'Akses ditolak', text: msg });
    })();
</script>
<?php unset($_SESSION['error']); endif; ?>

<script src="<?= BASE_URL ?>/public/assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var logout = document.getElementById('logoutBtn');
    if (!logout) return;
    logout.addEventListener('click', function (e) {
        e.preventDefault();
        var href = this.getAttribute('href');
        Swal.fire({
            title: 'Keluar',
            text: 'Apakah Anda yakin ingin logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function(result) {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});
</script>
</body>
</html>
