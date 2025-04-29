document.addEventListener('DOMContentLoaded', function() {
    // Count displayed competitions
    const countElements = () => {
        const total = document.querySelectorAll('.competition-row').length;
        const visible = document.querySelectorAll('.competition-row:not(.d-none)').length;
        document.getElementById('countTotal').textContent = total;
        document.getElementById('countShowing').textContent = visible;
    };

    // Simple search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            document.querySelectorAll('.competition-row').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.classList.toggle('d-none', searchTerm.length > 2 && !text.includes(searchTerm));
            });
            countElements();
        });
    }

    // Filter buttons
    document.getElementById('showUpcoming').addEventListener('click', function() {
        document.querySelectorAll('.competition-row').forEach(row => {
            const badge = row.querySelector('td:nth-child(2) .badge');
            row.classList.toggle('d-none', badge && badge.textContent === 'Proběhlo');
        });
        countElements();
    });

    document.getElementById('showAll').addEventListener('click', function() {
        document.querySelectorAll('.competition-row').forEach(row => {
            row.classList.remove('d-none');
        });
        countElements();
    });

    countElements();
});