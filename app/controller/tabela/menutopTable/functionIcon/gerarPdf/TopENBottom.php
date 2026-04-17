<?php
require_once __DIR__ . '/../../../../../../FPDF/fpdf.php';

class AppPDF extends FPDF
{
    private $configFiltros;
    function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        parent::__construct($orientation, $unit, $size);
        $titulo = ucfirst($_GET['tabela'] ?? 'Relatorio');
        $this->SetTitle(self::fixTexto($titulo));
    }
    public function setConfigFiltros($config)
    {
        $this->configFiltros = $config;
    }
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, self::fixTexto(ucfirst($_GET['tabela'] ?? '')), 0, 1, 'B');
        $subtitulo = [];
        $ignore = ['tabela', 'pdf_orientation', 'order_by', 'order_direction', 'show_cols'];
        foreach ($_GET as $key => $value) {
            if (in_array($key, $ignore) || $value === '' || is_array($value)) continue;
            if (strpos($key, '_de') !== false || strpos($key, '_ate') !== false) continue;
            $labelExibicao = $key;
            if ($this->configFiltros) {
                foreach ($this->configFiltros as $campo => $conf) {
                    if (($conf['label'] ?? $campo) === $key || $campo === $key) {
                        $labelExibicao = $conf['label'] ?? $campo;
                        break;
                    }
                }
            }
            $subtitulo[] = ucfirst($labelExibicao) . ": " . ucfirst($value);
        }
        if (!empty($subtitulo)) {
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 5, self::fixTexto(implode("  |  ", $subtitulo)), 0, 1, 'C');
            $this->Ln(4);
        }
    }

    function NomeFile()
    {
        $this->SetTitle(self::fixTexto(ucfirst($_GET['tabela'] ?? '')));
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, self::fixTexto('Página ') . $this->PageNo(), 0, 0, 'C');
    }
    private static function fixTexto($texto)
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', (string)$texto);
    }
}
