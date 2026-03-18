function statusReivindica(event) {
    if (event) event.preventDefault();
    const form = event.currentTarget || event.target;
    const inputData = document.querySelector('input[name="dia"]');
    const menssage = document.querySelector('#menssage-log');

    const hoje = new Date().toISOString().split('T')[0];
    if (inputData && inputData.value < hoje) {
        if (event) event.preventDefault();
        if (menssage) {
            menssage.textContent = 'Esta reivindicação já expirou.';
            menssage.style.color = 'red';
            menssage.style.fontWeight = 'bold';
        }
        return;
    }
    const url = form.getAttribute('action');
    const formData = new FormData(form);
    const acao = event.submitter ? event.submitter.getAttribute('data-status') : 'confirmado';

    formData.append('status', acao);
    if (url) loadPagePost(url, formData, true);
    else menssage.textContent = 'algo deu errado';
}
