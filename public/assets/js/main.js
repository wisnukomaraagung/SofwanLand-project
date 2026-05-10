// main.js — KMS Kontraktor

function toggleMenu() {
    const menu = document.getElementById('navMenu');
    if (menu) menu.classList.toggle('open');
}

// Confirm delete
function confirmDelete(url, nama) {
    if (confirm(`Hapus "${nama}"? Tindakan ini tidak dapat dibatalkan.`)) {
        window.location.href = url;
    }
}

// Format rupiah
function formatRupiah(angka) {
    return 'Rp ' + Number(angka).toLocaleString('id-ID');
}

// Auto-hide flash after 4 seconds
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 4000);
    }

    // Animate progress bars on load
    document.querySelectorAll('.progress-bar-fill').forEach(bar => {
        const w = bar.getAttribute('data-width') || '0';
        bar.style.width = '0%';
        setTimeout(() => { bar.style.width = w + '%'; }, 200);
    });
});
