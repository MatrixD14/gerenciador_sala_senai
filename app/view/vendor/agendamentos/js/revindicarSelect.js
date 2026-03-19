function enviaDadosRevindicar(event) {
    if (event) event.preventDefault();
    const form = event.currentTarget || event.target;
    const menssage = document.querySelector('.menssagen');
    const btnConfirm = document.getElementById('confirm');
    if (menssage) menssage.textContent = '';
    if (btnConfirm.hasAttribute('disabled')) {
        exibirErro(menssage, 'Ação não permitida para esta data ou horário.');
        return;
    }

    if (!form.checkValidity()) {
        exibirErro(menssage, 'Selecione um usuário para continuar.');
        return;
    }
    const url = form.getAttribute('action');
    const formData = new FormData(form);
    if (url) loadPagePost(url, formData, false);
    else console.error('Formulário sem atributo action definido.');
}
function exibirErro(elemento, texto) {
    if (!elemento) return;
    elemento.textContent = texto;
    elemento.style.fontWeight = 'bold';
    elemento.style.color = 'red';
    elemento.style.padding = '5px';
    elemento.style.background = '#fff';
}
