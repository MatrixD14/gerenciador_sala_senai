document.addEventListener('DOMContentLoaded', function () {
    const calendarGrid = document.getElementById('calendarGrid');
    const mesNome = document.getElementById('mesNome');
    let dataAtual = new Date();

    function renderizarCalendario() {
        calendarGrid.innerHTML = '';

        const ano = dataAtual.getFullYear();
        const mes = dataAtual.getMonth();
        const meses = [
            'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro',
        ];
        mesNome.innerText = `${ano}\n${meses[mes]}`;
        const diasSemana = ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'];
        diasSemana.forEach((dia) => {
            calendarGrid.innerHTML += `<div class="day-name">${dia}</div>`;
        });
        const primeiroDiaMes = new Date(ano, mes, 1).getDay();
        const totalDiasMes = new Date(ano, mes + 1, 0).getDate();
        const hoje = new Date();
        for (let i = 0; i < primeiroDiaMes; i++) {
            calendarGrid.innerHTML += `<div class="empty-slot"></div>`;
        }
        for (let dia = 1; dia <= totalDiasMes; dia++) {
            const dataNoLoop = new Date(ano, mes, dia);
            const hojeComparacao = new Date();
            hojeComparacao.setHours(0, 0, 0, 0);
            const isPassado = dataNoLoop < hojeComparacao;

            const isHoje = hoje.getDate() === dia && hoje.getMonth() === mes && hoje.getFullYear() === ano;
            const classeToday = isHoje ? 'today' : '';
            calendarGrid.innerHTML += `
    <button class="dias-btn ${classeToday} ${isPassado ? 'passado' : ''}" 
            ${isPassado ? 'disabled' : ''}>
        <span>${dia}</span>
    </button>`;
        }
    }
    document.getElementById('prevMonth').addEventListener('click', () => {
        dataAtual.setMonth(dataAtual.getMonth() - 1);
        renderizarCalendario();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
        dataAtual.setMonth(dataAtual.getMonth() + 1);
        renderizarCalendario();
    });

    renderizarCalendario();
});
