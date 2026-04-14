class TabelaConstrutor {
    // static construirLinhas(json) {
    //     const { dados, config } = json;
    //     if (!dados || dados.length === 0) return '';

    //     let html = '';
    //     dados.forEach((linha) => {
    //         const id = linha.id || 0;
    //         const rowClass = linha.is_locked ? 'row-locked' : '';

    //         html += `<tr data-id="${id}" class="${rowClass}">`;

    //         let colunas = [];
    //         if (config.colunas && Object.keys(config.colunas).length > 0) {
    //             colunas = Object.keys(config.colunas);
    //         } else if (config.especifico && Array.isArray(config.especifico)) {
    //             colunas = config.especifico;
    //         }

    //         colunas.forEach((col, index) => {
    //             const key = this.limparNome(col);
    //             let valor = linha[key] !== undefined ? linha[key] : '';

    //             if (this.isData(valor)) valor = this.formatarData(valor);

    //             const prefixo = index === 0 && linha.is_locked ? '⚠️ ' : '';
    //             html += `<td>${prefixo}${valor}</td>`;
    //         });

    //         html += `</tr>`;
    //     });
    //     return html;
    // }

    static construirLinhas(json) {
        const { dados, config } = json;
        if (!dados || dados.length === 0) return '';
        let colunasParaExibir = [];

        if (config.especifico && config.especifico.length > 0) {
            const aliasMap = config.especifico.map((expr) => this.limparNome(expr));

            if (config.colunas_visiveis && Array.isArray(config.colunas_visiveis)) {
                colunasParaExibir = config.colunas_visiveis.filter((alias) => aliasMap.includes(alias));
            } else {
                colunasParaExibir = aliasMap;
            }
        } else {
            const todasColunas = Object.keys(config.colunas || {});
            if (config.colunas_visiveis && Array.isArray(config.colunas_visiveis)) {
                colunasParaExibir = config.colunas_visiveis.filter((col) => todasColunas.includes(col));
            } else {
                colunasParaExibir = todasColunas;
            }
        }

        let html = '';
        dados.forEach((linha) => {
            const id = linha.id || 0;
            const rowClass = linha.is_locked ? 'row-locked' : '';

            html += `<tr data-id="${id}" class="${rowClass}">`;

            colunasParaExibir.forEach((alias, index) => {
                let valor = linha[alias] !== undefined ? linha[alias] : '';

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
