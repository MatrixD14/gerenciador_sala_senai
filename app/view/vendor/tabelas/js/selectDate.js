document.addEventListener('click', function (e) {
    const row = e.target.closest('.tabela tbody tr');
    if (!row) return;
    const alreadySelected = row.classList.contains('selected');
    document.querySelectorAll('.tabela tbody tr').forEach((tr) => tr.classList.remove('selected'));
    if (!alreadySelected) row.classList.add('selected');
});
