<?php

class RelatorioEngine
{
    private $config;
    private $slug;

    public function __construct($slug)
    {
        $this->slug = $slug;
        $path = __DIR__ . '/arrayTabelafiltroPDF.php';

        if (!file_exists($path)) {
            throw new Exception("Arquivo de configuração de filtros não encontrado.");
        }

        $allConfigs = require $path;
        $this->config = $allConfigs[$slug] ?? null;

        if (!$this->config) {
            throw new Exception("Configuração para a tabela '{$slug}' não encontrada.");
        }
    }

    public function renderForm(): string
    {

        // 1. Orientação da Página
        $html = $this->renaderOrientationPag();
        $html .= "<br>";
        $html .= $this->renderOrderOptions();
        $html .= "<hr>";

        foreach ($this->config['colunas'] as $name => $col) {
            $html .= "<div class='filtro-group'>";
            $html .= "<label><strong>Filtrar por " . ($col['label'] ?? ucfirst($name)) . ":</strong></label><br>";

            if ($col['type'] === 'date-range') {
                $html .= self::renderDateRange($name);
            } else {
                $html .= FormRenderer::renderField($name, $col, '', false, $this->config['colunas'], false, $this->slug);
            }
            $html .= "</div><br>";
        }

        $html .= $this->renderVisibilityCheckboxes();

        return $html;
    }
    private function renderDateRange($name): string
    {
        return "
            <div class='range-container'>
                <input type='date' name='{$name}_de' class='input-dados' placeholder='De'>
                <span>até</span>
                <input type='date' name='{$name}_ate' class='input-dados' placeholder='Até'>
            </div>";
    }
    private function renaderOrientationPag(): string
    {
        $html = "<div class='filtro-group'>";
        $html .= "<label><strong>Orientação da Página:</strong></label><br>";
        $html .= "<select name='pdf_orientation' class='select-dados'>";
        $html .= "<option value='L'>Paisagem (Deitado - Melhor para tabelas)</option>";
        $html .= "<option value='P'>Retrato (Em pé)</option>";
        $html .= "</select></div>";
        return $html;
    }

    private function renderOrderOptions(): string
    {
        $opcoes = $this->config['orderna'] ?? [];
        if (empty($opcoes)) return "";

        $html = "<div class='filtro-group'>";
        $html .= "<label><strong>Ordenar por:</strong></label><br>";
        $html .= "<div >";

        $html .= "<select name='order_by' class='select-dados' >";
        $html .= "<option value='id'>Padrão (ID)</option>";
        foreach ($opcoes as $campo) {
            $html .= "<option value='$campo'>" . ucfirst($campo) . "</option>";
        }
        $html .= "</select>";
        $html .= "<select name='order_direction' class='select-dados' >";
        $html .= "<option value='ASC'>Crescente (A-Z, 0-9)</option>";
        $html .= "<option value='DESC'>Decrescente (Z-A, 9-0)</option>";
        $html .= "</select>";

        $html .= "</div></div><br>";
        return $html;
    }

    private function renderVisibilityCheckboxes(): string
    {
        $options = $this->config['colunas_visiveis'] ?? [];
        $html = "<div class='filtro-group'><b>Colunas no PDF:</b><div class='grid-check'>";
        foreach ($options as $col) {
            $html .= "<label class='check-label'><input type='checkbox' name='show_cols[]' value='$col' checked> " . ucfirst($col) . "</label>";
        }
        return $html . "</div></div>";
    }
}
