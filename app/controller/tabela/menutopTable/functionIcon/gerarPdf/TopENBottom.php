<?php
require_once __DIR__ . '/../../../../../../FPDF/fpdf.php';

class AppPDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, self::fixTexto(ucfirst($_GET['tabela'] ?? '')), 0, 1, 'C');
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
