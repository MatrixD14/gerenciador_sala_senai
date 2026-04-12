function obterFiltrosForm() {
    const form = document.getElementById('formFiltro');
    if (!form) return {};
    const formData = new FormData(form);
    const filtros = {};
    for (let [key, value] of formData.entries()) {
        // Ignorar campos que não são filtros (ex: 'tabela', 'order_by', 'order_direction', 'show_cols[]')
        if (key === 'tabela' || key === 'order_by' || key === 'order_direction' || key === 'show_cols[]') {
            if (!filtros['_meta']) filtros['_meta'] = {};
            if (!filtros['_meta'][key]) filtros['_meta'][key] = [];
            filtros['_meta'][key].push(value);
            continue;
        }
        // Para campos com múltiplos valores (checkboxes)
        if (filtros[key]) {
            if (!Array.isArray(filtros[key])) filtros[key] = [filtros[key]];
            filtros[key].push(value);
        } else {
            filtros[key] = value;
        }
    }
    return filtros;
}
