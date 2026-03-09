document.addEventListener('focusin', (e) => {
    if (e.target && e.target.id === 'dia' && e.target.type === 'date') {
        const hoje = new Date().toISOString().split('T')[0];
        if (!e.target.min) {
            e.target.min = hoje;
        }
    }
});
