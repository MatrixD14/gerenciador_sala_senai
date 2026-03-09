document.addEventListener('change', (event) => {
    if (event.target.tagName === 'SELECT') {
        const select = event.target;
        const selectedOption = select.options[select.selectedIndex];

        const dependencias = selectedOption.dataset;
        Object.keys(dependencias).forEach((idCampoFilho) => {
            const campoFilho = document.getElementById(idCampoFilho);

            if (campoFilho) {
                campoFilho.value = dependencias[idCampoFilho];
                console.log(`Atualizado: #${idCampoFilho} -> ${dependencias[idCampoFilho]}`);
            }
        });
    }
});

document.querySelectorAll('select').forEach((s) => {
    if (s.selectedIndex !== -1) s.dispatchEvent(new Event('change', { bubbles: true }));
});
document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'form-pesquisa') {
        e.preventDefault();

        const tabela = document.getElementById('table-search').value;
        const termo = document.getElementById('search-input').value;

        const dados = new FormData();
        dados.append('search', termo);
        loadPagePost('/' + tabela, dados);
        PainelVoltar();
    }
});
function buttonVoltar() {
    if (window.history.length > 1) window.history.back();
    else location.reload();
}
function PainelVoltar() {
    const painel = document.querySelector('.Painel');
    if (painel) {
        painel.parentElement.innerHTML = '';
    }
}
