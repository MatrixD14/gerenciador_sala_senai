document.getElementById('formFiltro').onsubmit = function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    loadPagePost('/<?= $table ?>', formData, true);

    buttonVoltar();
};
