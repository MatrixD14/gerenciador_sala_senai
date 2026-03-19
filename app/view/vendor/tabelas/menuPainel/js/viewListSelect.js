document.addEventListener('click', function (e) {
    const trigger = e.target.closest('.custom-select-trigger');
    const container = e.target.closest('.custom-select-container');
    const option = e.target.closest('.custom-option');
    if (e.target.tagName === 'LABEL') {
        const targetId = e.target.getAttribute('for');
        const labelContainer = document.getElementById('container-' + targetId);
        if (labelContainer) {
            fecharTodosSelects(labelContainer);
            labelContainer.classList.toggle('open');
            const inputTxt = labelContainer.querySelector('.custom-select-trigger');
            if (inputTxt) inputTxt.focus();
            return;
        }
    }
    if (trigger) {
        const parent = trigger.closest('.custom-select-container');
        fecharTodosSelects(parent);
        parent.classList.toggle('open');
        return;
    }
    if (option && !option.classList.contains('option-disabled')) {
        const container = option.closest('.custom-select-container');
        const input = container.querySelector('input[type="hidden"]');
        const triggerDiv = container.querySelector('.custom-select-trigger');
        input.value = option.dataset.value;
        triggerDiv.value = option.innerText;
        container.classList.remove('open');
        input.dispatchEvent(new Event('change'));
        return;
    }

    if (!container) fecharTodosSelects();
});
function fecharTodosSelects(excecao = null) {
    document.querySelectorAll('.custom-select-container.open').forEach((c) => {
        if (c !== excecao) c.classList.remove('open');
    });
}
// 1. Delegamos o scroll apenas para a área de opções
document.addEventListener(
    'scroll',
    function (e) {
        const area = e.target;

        // Filtro seco: só executa se for a classe correta
        if (!area.classList || !area.classList.contains('options-scroll-area')) return;

        const sentinel = area.querySelector('.select-sentinel');

        // Se não tem sentinel ou já está carregando, para aqui
        if (!sentinel || sentinel.dataset.loading === 'true') return;

        // Cálculo objetivo: altura total - quanto rolou <= altura visível + margem
        if (area.scrollHeight - area.scrollTop <= area.clientHeight + 10) {
            carregarMaisOpcoes(sentinel, area);
        }
    },
    true,
);

function carregarMaisOpcoes(sentinel, container) {
    sentinel.dataset.loading = 'true';
    sentinel.innerText = 'Carregando...';

    const fd = new FormData();
    fd.append('acao', 'fetch_select_options');
    fd.append('tabela', sentinel.dataset.tabela);
    fd.append('coluna', sentinel.dataset.coluna);
    fd.append('value_col', sentinel.dataset.valueCol);
    fd.append('offset', sentinel.dataset.offset);
    fd.append('slug', sentinel.dataset.slug);
    fd.append('search', sentinel.dataset.search || '');
    fd.append('nome_campo_origem', sentinel.dataset.nomeCampoOrigem);

    fetch('/buscaList', { method: 'POST', body: fd })
        .then((r) => r.text())
        .then((html) => {
            if (!html.trim()) {
                sentinel.remove();
                return;
            }
            sentinel.remove();
            container.insertAdjacentHTML('beforeend', html);
        })
        .catch(() => {
            sentinel.dataset.loading = 'false';
            sentinel.innerText = 'Erro. Tente rolar de novo.';
        });
}
