document.addEventListener('focusin', (e) => {
    if (e.target && e.target.id === 'dia' && e.target.type === 'date') {
        const hoje = new Date().toISOString().split('T')[0];
        if (!e.target.min) {
            e.target.min = hoje;
        }
    }
});
function bloqueiarevindicar(event = null) {
    if (event) event.preventDefault();
    const form = event.currentTarget || event.target;
    const inputData = document.querySelector('input[name="dia"]');
    const btnConfirmar = document.getElementById('confirm');
    const menssage = document.querySelector('#menssage-log');
    const hoje = new Date().toISOString().split('T')[0];
    if (inputData && btnConfirmar) {
        const dataInput = inputData.value;
        if (dataInput < hoje) {
            if (event) event.preventDefault();
            if (menssage) {
                menssage.textContent = 'Não é possível mandar esta reivindicação porque a data já expirou.';
                menssage.style.color = 'red';
                menssage.style.fontWeight = 'bold';
            }
            if (btnConfirmar) {
                btnConfirmar.disabled = true;
                btnConfirmar.style.opacity = '0.5';
                btnConfirmar.style.cursor = 'not-allowed';
                btnConfirmar.innerText = 'Data Expirada';
            }
            return;
        }
        const url = form.getAttribute('action');
        const formData = new FormData(form);
        if (url) loadPagePost(url, formData, true);
        else menssage.textContent = 'algo deu errado';
    }
}
