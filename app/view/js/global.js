function toggleSenha(button) {
    const input = button.previousElementSibling;

    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈';
    } else {
        input.type = 'password';
        button.textContent = '👀';
    }
}

async function confirmaPassword(event) {
    event.preventDefault();
    const senha = document.querySelector('#senha').value;
    const confirmar = document.querySelector('#confirmaSenha').value;
    const error = document.querySelector('.error');
    if (senha !== confirmar) {
        error.textContent = 'A senha não são iquais.';
        return;
    }
    const form = document.querySelector('form');
    const dados = new FormData(form);
    form.action = '/cadastro';
    form.submit();
}
