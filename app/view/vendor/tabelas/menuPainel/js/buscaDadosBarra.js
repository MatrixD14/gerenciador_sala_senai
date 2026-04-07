document.addEventListener('input', function (e) {
    if (e.target.id === 'search') {
        const input = e.target;
        const termo = input.value;

        clearTimeout(input.searchTimeout);
        if (termo.length === 0 || termo.length >= 1) {
            input.searchTimeout = setTimeout(() => {
                if (typeof window.pesquisarTabela === 'function') {
                    window.pesquisarTabela(termo);
                } else {
                    if (window.tabelaState) {
                        window.tabelaState.search = termo;
                        window.initTabela(window.tabelaState.slug);
                    }
                }
            }, 400);
        }
    }
});
function toggleSearch() {
    const wrapper = document.getElementById('search-wrapper');
    const input = document.getElementById('search');
    wrapper.classList.toggle('escondido');
    if (!wrapper || !input) return;
    if (!wrapper.classList.contains('escondido')) {
        input.focus();
    } else {
        if (input.value !== '') {
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
}
