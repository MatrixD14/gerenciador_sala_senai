const menuIcon = document.querySelector('.menu-icon');
const menu = document.querySelector('.menu');

const cliceTabela = document.querySelector('.clice-tabela');
const menuTabelaList = document.querySelector('.menu-tabela');

const cliceLogin = document.querySelector('.clice-login');
const menuLoginList = document.querySelector('.menu-login');

menuIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    menu.classList.toggle('active');
});

cliceLogin.addEventListener('click', (e) => {
    e.stopPropagation();
    menuLoginList.classList.toggle('open');
    menuTabelaList.classList.remove('open');
});

cliceTabela.addEventListener('click', (e) => {
    e.stopPropagation();
    menuTabelaList.classList.toggle('open');
    menuLoginList.classList.remove('open');
});

document.addEventListener('click', (e) => {
    if (!menu.contains(e.target) && !menuIcon.contains(e.target)) {
        menu.classList.remove('active');
    }

    if (!menuLoginList.contains(e.target)) {
        menuLoginList.classList.remove('open');
    }

    if (!menuTabelaList.contains(e.target)) {
        menuTabelaList.classList.remove('open');
    }
});
