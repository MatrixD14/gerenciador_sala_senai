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
        $html .= $this->renderPageLimitOptions();
        $html .= "<br>";
        $html .= $this->renderOrderOptions();
        $html .= "<hr>";

        foreach ($this->config['colunas'] as $name => $col) {
            $html .= "<div class='filtro-group'>";
            $labelText = $col['label'] ?? ucfirst($name);

            if ($col['type'] === 'date-range') {
                $html .= "<p><strong>$labelText:</strong></p>";
                $html .= self::renderDateRange($name);
            } else {
                $html .= "<label for='$name'><strong>$labelText:</strong></label><br>";
                $html .= FormRenderer::renderField($name, $col, '', false, $this->config['colunas'], false, $this->slug);
            }
            $html .= "</div><br>";
        }

        $html .= $this->renderVisibilityCheckboxes();

        return $html;
    }
    public function renderPageLimitOptions(): string
    {
        $id = 'pagsLimite';
        $html = "<div class='filtro-group'>";
        $html .= "<label for='$id'><strong>Quantidade de Páginas:</strong></label><br>";
        $html .= "<select name='$id' id='$id' class='select-dados'>";
        $html .= "<option value='padrão'>Padrão (20)</option>";
        $html .= "<option value='1'>1 Página</option>";
        for ($i = 5; $i <= 50; $i += 5) {
            $html .= "<option value='$i'>$i Páginas</option>";
        }
        $html .= "</select></div>";
        return $html;
    }


    private function renderDateRange($name): string
    {
        $idDe = "{$name}_de";
        $idAte = "{$name}_ate";
        return "
        <div class='range-container'>
            <label for='$idDe' style='margin-right:5px;'>De:</label>
            <input type='date' name='{$name}_de' id='$idDe' class='input-dados'>
            <label for='$idAte' style='margin-right:5px;'>Até:</label>
            <input type='date' name='{$name}_ate' id='$idAte' class='input-dados'>
        </div>";
    }

    private function renaderOrientationPag(): string
    {
        $id = 'pdf_orientation';
        $html = "<div class='filtro-group'>";
        $html .= "<label for='$id'><strong>Orientação da Página:</strong></label><br>";
        $html .= "<select name='$id' id='$id' class='select-dados'>";
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
        $html .= "<p><strong>Ordenar por:</strong></p>";
        $html .= "<div >";

        $html .= "<select name='order_by' id='order_by' class='select-dados' >";
        $html .= "<option value='id'>Padrão (ID)</option>";
        foreach ($opcoes as $campo) {
            $html .= "<option value='$campo'>" . ucfirst($campo) . "</option>";
        }
        $html .= "</select>";
        $html .= "<select name='order_direction' id='order_direction' class='select-dados' >";
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
            $id = "show_cols_" . preg_replace('/[^a-zA-Z0-9_]/', '_', $col);
            $html .= "<label class='check-label' for='$id'>";
            $html .= "<input type='checkbox' name='show_cols[]' id='$id' value='$col' checked> " . ucfirst($col);
            $html .= "</label>";
        }
        return $html . "</div></div>";
    }
}
