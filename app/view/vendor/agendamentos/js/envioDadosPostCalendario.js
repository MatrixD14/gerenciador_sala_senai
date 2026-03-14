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
        if (!btn) return;
        const dia = btn.getAttribute('data-dia');
        const isPassado = btn.getAttribute('data-passado') === 'true';
        const temEvento = btn.classList.contains('possui-evento');
        if (temEvento) {
            const nomes = agendamentosGlobais[dia] || [];
            const formData = new FormData();
            formData.append('dia', dia);
            formData.append('mes', dataControleCalendario.getMonth() + 1);
            formData.append('ano', dataControleCalendario.getFullYear());

            nomes.forEach((info) => {
                formData.append('id[]', info[0]);
                formData.append('nomes[]', info[1]);
                formData.append('salas[]', info[2]);
                formData.append('periodos[]', info[3]);
            });

            loadPagePost('/menssageCalendario', formData);
            return;
        }
        if (isPassado) return;
        agendarDia(dia);
    });
    window.calendarioEventLoaded = true;
}
