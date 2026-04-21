document.addEventListener('click', async (e) => {
    const trigger = e.target.closest('.custom-select-trigger');
    const option = e.target.closest('.custom-option');
    const container = e.target.closest('.custom-select-container');
    if (trigger) {
        const container = trigger.closest('.custom-select-container');
        const wasOpen = container.classList.contains('open');
        fecharTodosSelects(container);
        if (!wasOpen) {
            container.classList.add('open');
            await window.initSelect(container);
            const selected = container.querySelector('.custom-option.selected');
            if (selected) {
                selected.scrollIntoView({ block: 'nearest' });
            }
        } else {
            container.classList.remove('open');
        }
        return;
    }

    if (option && !option.classList.contains('option-disabled')) {
        const container = option.closest('.custom-select-container');
        const inputHidden = container.querySelector('input[type="hidden"]');
        const triggerInput = container.querySelector('.custom-select-trigger');

        const val = option.dataset.value;
        const label = option.innerText;

        inputHidden.value = val;
        triggerInput.value = label;
        Object.keys(option.dataset).forEach((key) => {
            if (key !== 'value' && key !== 'pgOffset' && key !== 'blocoIdx') {
                const inputDependente = document.querySelector(`input[name="${key}"]`);
                if (inputDependente) {
                    inputDependente.value = option.dataset[key];
                    inputDependente.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        });

        // Gerencia classes de "selected" visualmente
        container.querySelectorAll('.custom-option').forEach((opt) => opt.classList.remove('selected'));
        option.classList.add('selected');
        container.classList.remove('open');
        inputHidden.dispatchEvent(new Event('change', { bubbles: true }));
        return;
    }

    // 3. Fechar ao clicar fora
    if (!container) {
        fecharTodosSelects();
    }
});
function fecharTodosSelects(excecao = null) {
    document.querySelectorAll('.custom-select-container.open').forEach((c) => {
        if (c !== excecao) c.classList.remove('open');
    });
}
