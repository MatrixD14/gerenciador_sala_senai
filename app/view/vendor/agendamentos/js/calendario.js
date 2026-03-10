let dataControleCalendario = new Date();
function initCalendario() {
  const calendarGrid = document.getElementById("calendarGrid");
  const mesNome = document.getElementById("mesNome");
  if (!calendarGrid || !mesNome) return;

  function renderizarCalendario() {
    calendarGrid.innerHTML = "";

    const ano = dataControleCalendario.getFullYear();
    const mes = dataControleCalendario.getMonth();
    const meses = [
      "Janeiro",
      "Fevereiro",
      "Março",
      "Abril",
      "Maio",
      "Junho",
      "Julho",
      "Agosto",
      "Setembro",
      "Outubro",
      "Novembro",
      "Dezembro",
    ];
    mesNome.innerText = `${ano}\n${meses[mes]}`;
    const diasSemana = ["DOM", "SEG", "TER", "QUA", "QUI", "SEX", "SÁB"];
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

      const classeToday = isHoje ? "today" : "";

      calendarGrid.innerHTML += `
    <button class="dias-btn ${classeToday} ${isPassado ? "passado" : ""}" 
            ${isPassado ? "disabled" : ""} data-dia=${dia}>
        <span>${dia}</span>
    </button>`;
    }
    const celulasOcupadas = primeiroDiaMes + totalDiasMes;
    const celulasRestantes = 42 - celulasOcupadas;

    for (let i = 0; i < celulasRestantes; i++) {
      calendarGrid.innerHTML += `<div class="empty-slot"></div>`;
    }
  }

  document.getElementById("prevMonth").onclick = () => {
    dataControleCalendario.setMonth(dataControleCalendario.getMonth() - 1);
    renderizarCalendario();
  };

  document.getElementById("nextMonth").onclick = () => {
    dataControleCalendario.setMonth(dataControleCalendario.getMonth() + 1);
    renderizarCalendario();
  };

  renderizarCalendario();
}
function agendarDia(dia) {
  const ano = dataControleCalendario.getFullYear();
  const mes = dataControleCalendario.getMonth() + 1;
  const formData = new FormData();
  formData.append("dia", dia);
  formData.append("mes", mes);
  formData.append("ano", ano);
  formData.append("tabela", "agendamentos");
  loadPagePost("/insert", formData);
}
document.addEventListener("click", (e) => {
  const btn = e.target.closest(".dias-btn");
  if (btn && !btn.disabled) {
    const diaClicado = btn.getAttribute("data-dia");
    agendarDia(diaClicado);
  }
});
document.addEventListener("DOMContentLoaded", initCalendario);
