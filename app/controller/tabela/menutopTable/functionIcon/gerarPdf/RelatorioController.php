<?php
require_once __DIR__ . '/../../../../../../FPDF/fpdf.php';
require_once __DIR__ . '/../../../arrayTables.php';

class RelatorioController
{
    public function gerarPDF()
    {
        $slug = $_GET['tabela'] ?? 'agendamentos';
        $orientation = $_GET['pdf_orientation'] ?? 'L';
        $userLogin = $_SESSION['user_id'] ?? null;
        $path = __DIR__ . '/arrayTabelafiltroPDF.php';
        $allConfigs = file_exists($path) ? require $path : [];
        $configDaTabela = $allConfigs[$slug]['colunas'] ?? [];
        $dadosTabela = Tabelas::getDadosParaPDF($slug, $userLogin, 500, 0);

        if (isset($dadosTabela['erro'])) {
            die('Erro ao carregar dados: ' . $dadosTabela['erro']);
        }

        $dados = $dadosTabela['dados'];
        $config = $dadosTabela['config'];
        $colunas = $this->getColunasExibicao($config);

        $pdf = new AppPDF($orientation, 'mm', 'A4');
        $pdf->setConfigFiltros($configDaTabela);
        $pdf->AliasNbPages();
        $pdf->AddPage($orientation);

        // Calcula larguras proporcionalmente
        $larguraDisponivel = $pdf->GetPageWidth() - 20;
        $larguras = $this->calcularLarguras($colunas, $larguraDisponivel);

        $limiteQuebra = ($orientation === 'L') ? 180 : 270;
        // Cabeçalho
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        foreach ($colunas as $i => $col) {
            $pdf->Cell($larguras[$i], 8, self::fixTexto($col['titulo']), 1, 0, 'C', true);
        }
        $pdf->Ln();

        // Dados
        $pdf->SetFont('Arial', '', 9);
        foreach ($dados as $linha) {
            if ($pdf->GetY() > $limiteQuebra) {
                $pdf->AddPage($orientation);
                $pdf->SetFont('Arial', 'B', 10);
                foreach ($colunas as $i => $col) {
                    $pdf->Cell($larguras[$i], 8, self::fixTexto($col['titulo']), 1, 0, 'C', true);
                }
                $pdf->Ln();
                $pdf->SetFont('Arial', '', 9);
            }

            foreach ($colunas as $i => $col) {
                $campo = $col['campo'];
                $valor = $linha[$campo] ?? '';

                if ($this->isData($valor)) {
                    $valor = $this->formatarData($valor);
                }
                $textoLimpo = str_replace(["\r", "\n"], ' ', (string)$valor);
                $textoCélula = self::fixTexto($textoLimpo);
                $larguraTexto = $pdf->GetStringWidth($textoCélula);
                if ($larguraTexto > $larguras[$i] - 2) {
                    while ($pdf->GetStringWidth($textoCélula . '...') > $larguras[$i] - 2) {
                        $textoCélula = substr($textoCélula, 0, -1);
                    }
                    $textoCélula .= '...';
                }

                $pdf->Cell($larguras[$i], 7, $textoCélula, 1);
            }
            $pdf->Ln();
        }

        $pdf->Output('I', "relatorio_{$slug}.pdf");
        exit;
    }

    private static function fixTexto($texto)
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', (string)$texto);
    }

    private function getColunasExibicao($config)
    {
        $colunas = [];
        $showCols = $_GET['show_cols'] ?? null;
        $permitidas = null;
        if ($showCols) {
            if (is_array($showCols)) $permitidas = $showCols;
            else {
                $decoded = json_decode($showCols, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $permitidas = $decoded;
                } else {
                    // Caso seja uma string separada por vírgula
                    $permitidas = explode(',', $showCols);
                }
            }
        }
        $filtroConfigPath = __DIR__ . '/arrayTabelafiltroPDF.php';
        $allFiltros = file_exists($filtroConfigPath) ? require $filtroConfigPath : [];
        $slug = $_GET['tabela'] ?? '';
        $configFiltro = $allFiltros[$slug]['colunas'] ?? [];
        $colunasDoBanco = [];
        if (!empty($config['especifico'])) {
            foreach ($config['especifico'] as $expr) {
                $colunasDoBanco[] = $this->extrairAlias($expr);
            }
        } else {
            $colunasDoBanco = array_keys($config['colunas']);
        }

        foreach ($colunasDoBanco as $campo) {
            $infoFiltro = null;
            if (isset($configFiltro[$campo])) {
                $infoFiltro = $configFiltro[$campo];
            } else {
                foreach ($configFiltro as $keyFiltro => $detalhes) {
                    if (strtolower($detalhes['label'] ?? '') === strtolower($campo) || strtolower($keyFiltro) === strtolower($campo)) {
                        $infoFiltro = $detalhes;
                        break;
                    }
                }
            }

            if ($infoFiltro && isset($infoFiltro['unique']) && $infoFiltro['unique'] === true) {
                $nomeNaUrl = strtolower($infoFiltro['label'] ?? $campo);

                $valorFiltro = null;
                foreach ($_GET as $keyGet => $valGet) {
                    if (strtolower($keyGet) === $nomeNaUrl) {
                        $valorFiltro = $valGet;
                        break;
                    }
                }

                if (!empty($valorFiltro)) continue;
            }

            if ($permitidas !== null)
                if (!in_array(strtolower($campo), $permitidas)) continue;

            $titulo = $infoFiltro['label'] ?? ucfirst(str_replace('_', ' ', $campo));
            $colunas[] = ['campo' => $campo, 'titulo' => $titulo];
        }

        return $colunas;
    }

    private function extrairAlias($expr)
    {
        if (stripos($expr, ' as ') !== false) {
            $parts = preg_split('/\s+as\s+/i', $expr);
            return trim(end($parts));
        }
        if (strpos($expr, '.') !== false) {
            $parts = explode('.', $expr);
            return trim(end($parts));
        }
        return trim($expr);
    }

    private function calcularLarguras($colunas, $larguraTotal)
    {
        $qtd = count($colunas);
        $larguraBase = $larguraTotal / $qtd;
        $larguras = array_fill(0, $qtd, $larguraBase);
        // Opcional: ajustar larguras mínimas/máximas
        return $larguras;
    }

    private function isData($valor)
    {
        return is_string($valor) && preg_match('/^\d{4}-\d{2}-\d{2}/', $valor);
    }

    private function formatarData($valor)
    {
        $parts = explode(' ', $valor);
        $data = $parts[0];
        $hora = $parts[1] ?? '';
        $partesData = explode('-', $data);
        $dataFormatada = $partesData[2] . '/' . $partesData[1] . '/' . $partesData[0];
        return $dataFormatada . ($hora ? ' ' . substr($hora, 0, 5) : '');
    }
}
