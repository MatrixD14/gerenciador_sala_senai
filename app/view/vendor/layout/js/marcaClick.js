document.addEventListener('click', function (e) {
    const link = e.target.closest('.ajax-link');

    if (!link) return;

    e.preventDefault();

    const url = link.getAttribute('href');

    loadPage(url);
});
function setActiveMenu(url) {
    document.querySelectorAll('.menu a').forEach((link) => {
        link.classList.remove('active');
        const href = link.getAttribute('href').trim();
        if (href === url) {
            link.classList.add('active');
        }
    });
}
document.addEventListener('contentUpdated', () => {
    setActiveMenu(location.pathname);
});
