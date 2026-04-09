class TabelaConstrutor {
    static construirLinhas(json) {
        const { dados, config } = json;
        if (!dados || dados.length === 0) return '';

        let html = '';
        dados.forEach((linha) => {
            const id = linha.id || 0;
            const rowClass = linha.is_locked ? 'row-locked' : '';

            html += `<tr data-id="${id}" class="${rowClass}">`;

            let colunas = [];
            if (config.especifico && Array.isArray(config.especifico)) {
                colunas = config.especifico;
            } else if (config.colunas) {
                colunas = Object.keys(config.colunas);
            }

            colunas.forEach((col, index) => {
                const key = this.limparNome(col);
                let valor = linha[key] !== undefined ? linha[key] : '';

                if (this.isData(valor)) valor = this.formatarData(valor);

                const prefixo = index === 0 && linha.is_locked ? '⚠️ ' : '';
                html += `<td>${prefixo}${valor}</td>`;
            });

            html += `</tr>`;
        });
        return html;
    }

    static limparNome(n) {
        if (n.includes(' as ')) return n.split(' as ').pop().trim();
        if (n.includes('.')) return n.split('.').pop().trim();
        return n;
    }

    static isData(v) {
        return /^\d{4}-\d{2}-\d{2}/.test(v);
    }

    static formatarData(v) {
        const parts = v.split(' ');
        const [y, m, d] = parts[0].split('-');
        return `${d}/${m}/${y}` + (parts[1] ? ` ${parts[1].substring(0, 5)}` : '');
    }
}
