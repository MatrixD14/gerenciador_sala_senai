let dataControleCalendario = new Date();
let data_atual = new Date();
let agendamentosGlobais = {};

const ComponentesCalendario = {
    gerarDia(dia, isPassado, isHoje, temEvento) {
        const classeEvento = temEvento ? 'possui-evento' : '';
        const classeHoje = isHoje ? 'today' : '';
        let classeTemporal = '';
        if (isPassado) classeTemporal = temEvento ? 'passado-dados' : 'passado';
        const htmlEvento = temEvento ? `<span class="dot-evento"></span>` : '';
        return `
            <button class="dias-btn ${classeEvento} ${classeHoje} ${classeTemporal}" 
                    data-dia="${dia}" 
                    data-passado="${isPassado}">
                <span>${dia}</span>
                ${htmlEvento}
            </button>`;
    },

    gerarSlotVazio: () => `<div class="empty-slot"></div>`,

    gerarCabecalho: () =>
        ['DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SÁB'].map((dia) => `<div class="day-name">${dia}</div>`).join(''),
};
async function renderizarCalendario() {
    const grid = document.getElementById('calendarGrid');
    const labelMes = document.getElementById('mesNome');
    if (!grid || !labelMes) return;

    grid.innerHTML = '<div class="loading">Carregando...</div>';

    const ano = dataControleCalendario.getFullYear();
    const mesIndex = dataControleCalendario.getMonth();
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);
    agendamentosGlobais = await buscarAgendamentosDoServidor(mesIndex + 1, ano);

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
    labelMes.innerText = `${ano}\n${meses[mesIndex]}`;
    let htmlFinal = ComponentesCalendario.gerarCabecalho();

    const primeiroDiaMes = new Date(ano, mesIndex, 1);
    const primeiroDiaSemana = primeiroDiaMes.getDay();
    const totalDiasMes = new Date(ano, mesIndex + 1, 0).getDate();

    for (let i = 0; i < primeiroDiaSemana; i++) htmlFinal += ComponentesCalendario.gerarSlotVazio();

    for (let dia = 1; dia <= totalDiasMes; dia++) {
        const dataNoLoop = new Date(ano, mesIndex, dia);
        const isPassado = dataNoLoop < hoje;
        const isHoje = dataNoLoop.toDateString() === hoje.toDateString();
        const listaNomes = agendamentosGlobais[dia] || [];
        const temEvento = listaNomes.length > 0;

        htmlFinal += ComponentesCalendario.gerarDia(dia, isPassado, isHoje, temEvento);
    }

    const totalSlotsAteAgora = primeiroDiaSemana + totalDiasMes;
    const restante = Math.ceil(totalSlotsAteAgora / 7) * 7 - totalSlotsAteAgora;

    for (let i = 0; i < restante; i++) {
        htmlFinal += ComponentesCalendario.gerarSlotVazio();
    }

    grid.innerHTML = htmlFinal;
    atualizarNavegacao(ano, mesIndex);
}

function atualizarNavegacao(ano, mes) {
    const btnAnt = document.getElementById('prevMonth');
    const btnProx = document.getElementById('nextMonth');

    const dataAlvo = new Date(ano, mes, 1);
    const limitePassado = new Date(data_atual.getFullYear(), data_atual.getMonth() - 12, 1);
    const limiteFuturo = new Date(data_atual.getFullYear(), data_atual.getMonth() + 12, 1);

    if (btnAnt) {
        const desativar = dataAlvo <= limitePassado;
        btnAnt.disabled = desativar;
        btnAnt.style.visibility = desativar ? 'hidden' : 'visible';
    }
    if (btnProx) {
        const desativar = dataAlvo >= limiteFuturo;
        btnProx.disabled = desativar;
        btnProx.style.visibility = desativar ? 'hidden' : 'visible';
    }
}

async function buscarAgendamentosDoServidor(mes, ano) {
    try {
        const response = await fetch(`/CalendarioAgendamento?mes=${mes}&ano=${ano}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });

        if (!response.ok) throw new Error(`Erro status: ${response.status}`);

        return await response.json();
    } catch (e) {
        console.error('Falha ao obter dados do site:', e);
        return {};
    }
}
function initCalendario() {
    const btnAnt = document.getElementById('prevMonth');
    const btnProx = document.getElementById('nextMonth');

    if (btnAnt)
        btnAnt.onclick = () => {
            dataControleCalendario.setMonth(dataControleCalendario.getMonth() - 1);
            renderizarCalendario();
        };

    if (btnProx)
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
