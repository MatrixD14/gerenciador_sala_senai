<?php
require_once __DIR__ . '/../../../../../../FPDF/fpdf.php';
require_once __DIR__ . '/../../../arrayTables.php';

class RelatorioController
{
    public function gerarPDF()
    {
        $slug = $_GET['tabela'] ?? 'agendamentos';
        $orientation = $_GET['pdf_orientation'] ?? 'L';
        $pagsLimite = (int)($_GET['pags_limite'] ?? 0);
        $userLogin = $_SESSION['user_id'] ?? null;
        $path = __DIR__ . '/arrayTabelafiltroPDF.php';
        $allConfigs = file_exists($path) ? require $path : [];
        $configDaTabela = $allConfigs[$slug]['colunas'] ?? [];
        $registrosPorPagina = ($orientation === 'L') ? 21 : 35;
        $limitSQL = $pagsLimite > 0 ? $pagsLimite * $registrosPorPagina : 500;

        $dadosTabela = Tabelas::getDadosParaPDF($slug, $userLogin, $limitSQL, 0);

        if (isset($dadosTabela['erro']))
            die('Erro: ' . $dadosTabela['erro']);


        $dados = $dadosTabela['dados'];
        $config = $dadosTabela['config'];
        $colunas = $this->getColunasExibicao($config, $configDaTabela);

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

    private function getColunasExibicao($config, $configFiltro)
    {
        $colunas = [];
        $showCols = $_GET['show_cols'] ?? null;
        $permitidas = $this->normalizarPermitidas($showCols);


        $colunasDoBanco = [];
        if (!empty($config['especifico'])) {
            foreach ($config['especifico'] as $expr) {
                $colunasDoBanco[] = $this->extrairAlias($expr);
            }
        } else {
            foreach ($config['colunas'] as $campo => $info) {
                $colunasDoBanco[] = $info['maskname'] ?? ($info['masckname'] ?? $campo);
            }
        }

        foreach ($colunasDoBanco as $campo) {
            $infoFiltro = null;
            $chaveOriginalFiltro = $campo;
            if (isset($configFiltro[$campo])) {
                $infoFiltro = $configFiltro[$campo];
            } else {
                foreach ($configFiltro as $keyFiltro => $detalhes) {
                    $mName = $detalhes['maskname'] ?? ($detalhes['masckname'] ?? null);
                    if ($mName === $campo || strtolower($detalhes['label'] ?? '') === strtolower($campo) || strtolower($keyFiltro) === strtolower($campo)) {
                        $infoFiltro = $detalhes;
                        $chaveOriginalFiltro = $keyFiltro;
                        break;
                    }
                }
            }

            if ($infoFiltro &&    $this->OcultarPorFiltro($chaveOriginalFiltro, $infoFiltro, $campo)) continue;


            if ($permitidas !== null) {
                $campoLower = strtolower($campo);
                $chaveLower = strtolower($chaveOriginalFiltro);
                if (!in_array($campoLower, $permitidas) && !in_array($chaveLower, $permitidas)) {
                    continue;
                }
            }

            $titulo = $infoFiltro['label'] ?? ucfirst(str_replace('_', ' ', $campo));
            $colunas[] = ['campo' => $campo, 'titulo' => $titulo];
        }

        return $colunas;
    }
    private function OcultarPorFiltro($chaveOriginalFiltro, $infoFiltro, $campo)
    {
        if (isset($infoFiltro['unique']) && $infoFiltro['unique'] === true) {
            $nomeNaUrl = strtolower($infoFiltro['label'] ?? $campo);
            $maskNameConfig = strtolower($infoFiltro['maskname'] ?? ($infoFiltro['masckname'] ?? ''));
            foreach ($_GET as $keyGet => $valGet) {
                $keyLower = strtolower($keyGet);
                if ($keyLower === $nomeNaUrl || $keyLower === strtolower($chaveOriginalFiltro) || ($maskNameConfig && $keyLower === $maskNameConfig) && !empty($valGet))
                    return true;
            }
        }
        return false;
    }
    private function normalizarPermitidas($showCols)
    {
        if ($showCols)  return null;
        if (is_array($showCols)) return array_map('strtolower', $showCols);
        $decoded = json_decode($showCols, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_map('strtolower', $decoded);
        }
        return array_map('strtolower', explode(',', $showCols));
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
