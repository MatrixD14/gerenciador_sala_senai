let dataControleCalendario = new Date();
let data_atual = new Date();
function initCalendario() {
    const calendarGrid = document.getElementById('calendarGrid');
    const mesNome = document.getElementById('mesNome');
    const btnAnt = document.getElementById('prevMonth');
    const btnProx = document.getElementById('nextMonth');
    if (!calendarGrid || !mesNome) return;

    function renderizarCalendario() {
        calendarGrid.innerHTML = '';

        const ano = dataControleCalendario.getFullYear();
        const mes = dataControleCalendario.getMonth();

        const limitePassado = new Date();
        limitePassado.setMonth(data_atual.getMonth() - 12);
        const isMuitoAntigo = ano <= limitePassado.getFullYear() && mes <= limitePassado.getMonth();
        if (btnAnt) {
            btnAnt.disabled = isMuitoAntigo;
            btnAnt.style.visibility = isMuitoAntigo ? 'hidden' : 'visible';
        }
        const limiteFuturo = new Date();
        limiteFuturo.setMonth(data_atual.getMonth() + 12);
        const isLimiteFuturo = ano >= limiteFuturo.getFullYear() && mes >= limiteFuturo.getMonth();

        if (btnProx) {
            btnProx.disabled = isLimiteFuturo;
            btnProx.style.visibility = isLimiteFuturo ? 'hidden' : 'visible';
        }
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
        hoje.setHours(0, 0, 0, 0);
        for (let i = 0; i < primeiroDiaMes; i++) {
            calendarGrid.innerHTML += `<div class="empty-slot"></div>`;
        }
        for (let dia = 1; dia <= totalDiasMes; dia++) {
            const dataNoLoop = new Date(ano, mes, dia);
            const isPassado = dataNoLoop < hoje;
            const isHoje = dataNoLoop.getTime() === hoje.getTime();

            const classeToday = isHoje ? 'today' : '';

            calendarGrid.innerHTML += `
    <button class="dias-btn ${classeToday} ${isPassado ? 'passado' : ''}" 
            ${isPassado ? 'disabled' : ''} data-dia=${dia}>
        <span>${dia}</span>
    </button>`;
        }
        const celulasOcupadas = primeiroDiaMes + totalDiasMes;
        const celulasRestantes = 42 - celulasOcupadas;

        for (let i = 0; i < celulasRestantes; i++) {
            calendarGrid.innerHTML += `<div class="empty-slot"></div>`;
        }
    }

    btnAnt.onclick = () => {
        dataControleCalendario.setMonth(dataControleCalendario.getMonth() - 1);
        renderizarCalendario();
    };

    btnProx.onclick = () => {
        dataControleCalendario.setMonth(dataControleCalendario.getMonth() + 1);
        renderizarCalendario();
    };

    renderizarCalendario();
}
function checkAndInitCalendario() {
    if (document.getElementById('calendarGrid')) {
        initCalendario();
    }
}
document.addEventListener('DOMContentLoaded', checkAndInitCalendario);
document.addEventListener('contentUpdated', checkAndInitCalendario);
