<?php
// require_once __DIR__ . '/../../fpdf/fpdf.php';

class RelatorioController
{
    public function gerarPDF()
    {
        // Pega parâmetros (se necessário)
        $tabela = $_GET['tabela'] ?? '';
        $id = $_GET['id'] ?? '';
        $name = $_GET['name'] ?? '';

        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 12);
        for ($i = 1; $i <= 40; $i++)
            $pdf->Cell(0, 10, 'Printing line number ' . $i, 0, 1);
        $pdf->Output();
        exit;
    }
}
