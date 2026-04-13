document.addEventListener('click', function (e) {
    const row = e.target.closest('.tabela tbody tr');
    if (row) {
        const alreadySelected = row.classList.contains('selected');
        document.querySelectorAll('.tabela tbody tr').forEach((tr) => tr.classList.remove('selected'));
        if (!alreadySelected) row.classList.add('selected');
    }

    const icon = e.target.closest('.icon-table');
    if (icon) {
        const action = icon.getAttribute('data-action');
        const table = icon.getAttribute('data-table');
        const selectedRow = document.querySelector('.tabela tbody tr.selected');

        if (
            !selectedRow &&
            action !== 'add' &&
            action !== 'reload' &&
            action !== 'agenda' &&
            action !== 'view' &&
            action !== 'filtro'
        ) {
            alert('Selecione uma linha primeiro!');
            return;
        }
        const id = selectedRow ? selectedRow.getAttribute('data-id') : null;
        const name = selectedRow
            ? selectedRow.getAttribute('data-name') || selectedRow.getAttribute('data-user')
            : null;

        executarAcao(action, table, id, name);
    }
});
