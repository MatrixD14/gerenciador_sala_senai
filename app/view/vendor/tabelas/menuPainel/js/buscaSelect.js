document.addEventListener('input', function (e) {
    if (e.target.classList.contains('custom-search')) {
        const input = e.target;
        const container = input.closest('.custom-select-container');
        const scrollArea = container.querySelector('.options-scroll-area');
        const term = input.value.trim();
        clearTimeout(input.searchTimeout);

        if (term.length === 0 || term.length >= 2) {
            input.searchTimeout = setTimeout(() => {
                if (typeof window.pesquisarSelect === 'function') {
                    window.pesquisarSelect(term);
                } else if (window.SelectState) {
                    window.SelectState.search = term;
                    window.initSelect(window.SelectState.slug);
                }
            }, 300);
        }
    }
});
