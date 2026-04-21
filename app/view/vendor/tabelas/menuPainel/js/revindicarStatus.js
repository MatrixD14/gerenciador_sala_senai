function statusReivindica(event) {
    if (event) event.preventDefault();
    const form = event.currentTarget || event.target;
    const inputData = document.querySelector('input[name="dia"]');
    const menssage = document.querySelector('#menssage-log');

    const statusAtual = form.getAttribute('data-status-atual');
    if (statusAtual !== 'pendente') {
        if (menssage) {
            menssage.textContent = `Esta solicitação já está ${statusAtual.toUpperCase()} e não pode ser alterada.`;
            menssage.style.color = 'orange';
        }
        return;
    }

    const url = form.getAttribute('action');
    const formData = new FormData(form);
    const acao = event.submitter ? event.submitter.getAttribute('data-status') : 'confirmado';

    formData.append('status', acao);
    console.log('Enviando ID:', formData.get('id'), 'Status:', acao);
    if (url) loadPagePost(url, formData, true);
    else menssage.textContent = 'algo deu errado';
}
