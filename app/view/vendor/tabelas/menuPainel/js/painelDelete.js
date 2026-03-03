const actions = {
    'icon-lixeira': (table, id) => {
        const painel = document.querySelector('.Painel-delete');
        const info = document.getElementById('delete-info');

        info.textContent = `Deseja excluir o ID ${id} da tabela ${table}?`;
        painel.style.display = 'block';
        painel.dataset.table = table;
        painel.dataset.id = id;
    },

    'icon-reload': () => location.reload(),
};
document.getElementById('cancel-delete').addEventListener('click', function () {
    document.querySelector('.Painel-delete').style.display = 'none';
});
document.getElementById('confirm-delete').addEventListener('click', function () {
    const painel = document.querySelector('.Painel-delete');
    const table = painel.dataset.table;
    const id = painel.dataset.id;

    fetch('/app/controller/tablas.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'icon-lixeira',
            table: table,
            id: id,
        }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.status) {
                const row = document.querySelector(`.tabela tbody tr[data-id="${id}"]`);
                if (row) row.remove();

                painel.style.display = 'none';
            } else {
                alert(data.msg);
            }
        });
});
