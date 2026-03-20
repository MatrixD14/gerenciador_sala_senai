function toggleFooter() {
    const footer = document.querySelector('.footer');
    if (footer) {
        footer.classList.toggle('hidden');
        const isHidden = footer.classList.contains('hidden');
        localStorage.setItem('footerHidden', isHidden);
    }
}
