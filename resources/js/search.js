document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('searchGedung');
    if (!input) return;

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.content-card[data-nama]').forEach(card => {
            card.style.display = card.dataset.nama.includes(q) ? '' : 'none';
        });
    });
});
