function agendarDia(dia) {
    const ano = dataControleCalendario.getFullYear();
    const mes = dataControleCalendario.getMonth() + 1;
    const formData = new FormData();
    formData.append('dia', dia);
    formData.append('mes', mes);
    formData.append('ano', ano);
    formData.append('tabela', 'agendamentos');
    loadPagePost('/insert', formData);
}
if (!window.calendarioEventLoaded) {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.dias-btn');
        if (btn && !btn.disabled && !btn.classList.contains('possui-evento')) {
            const diaClicado = btn.getAttribute('data-dia');
            agendarDia(diaClicado);
        }
    });
    window.calendarioEventLoaded = true;
}
