document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const productCards = document.querySelectorAll('.product-card');

    // Simpele zoekfilter functionaliteit
    searchInput.addEventListener('input', (e) => {
        const filterText = e.target.value.toLowerCase();

        productCards.forEach((card, index) => {
            // Sla de eerste "producten" placeholder over indien gewenst
            if (index === 0) return;

            const naam = card.querySelector('.naam-box')?.textContent.toLowerCase() || '';
            if (naam.includes(filterText)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
});