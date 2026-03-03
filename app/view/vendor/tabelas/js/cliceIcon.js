document.addEventListener('click', function (e) {
    const icon = e.target.closest('.icon-table');
    if (!icon) return;

    const action = icon.dataset.action;
    const table = icon.dataset.table;
    const id = getSelectedId();

    if (!id && action !== 'icon-mais') {
        alert('Selecione uma linha primeiro');
        return;
    }

    actions[action]?.(table, id);
});
function getSelectedId() {
    const selected = document.querySelector('.tabela tbody tr.selected');
    return selected ? selected.dataset.id : null;
}
