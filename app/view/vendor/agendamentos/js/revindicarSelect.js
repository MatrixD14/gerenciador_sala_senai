function enviaDadosRevindicar(event) {
    if (event) event.preventDefault();
    console.log('carrego no script');
    const form = document.querySelector('form.Painel');
    if (form) {
        const url = form.getAttribute('action');
        const formData = new FormData(form);
        if (url) {
            loadPagePost(url, formData);
        } else {
            console.error('Formulário sem atributo action definido.');
        }
    }
}
