<?php
class completeList
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require_once __DIR__ . '/../../../arrayTables.php';
    }
    public static function buscaDadosList()
    {
        self::loadConfig();
        if (isset($_POST['acao']) && $_POST['acao'] === 'fetch_select_options') {
            try {
                $tabela = $_POST['tabela'];
                $colValue = $_POST['value_col'];
                $colLabel = $_POST['coluna'];
                $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
                $slug     = $_POST['slug'] ?? '';
                $nomeCampoOrigem = $_POST['nome_campo_origem'] ?? '';

                $limit = 50;
                $extraCols = [];
                if (!empty($slug) && isset(self::$inforDate[$slug]['colunas'])) {
                    foreach (self::$inforDate[$slug]['colunas'] as $nomeCol => $conf) {
                        if (($conf['depends'] ?? '') === $nomeCampoOrigem) {
                            $extraCols[] = $nomeCol;
                        }
                    }
                }
                $searchTerm = $_POST['search'] ?? '';

                $res = BuscaInfoUser::buscaBancoInfo($tabela, $colValue, $colLabel, $offset, $limit, $extraCols, $searchTerm);

                $html = "";
                $count = 0;

                while ($row = $res->fetch_assoc()) {
                    $val = $row[$colValue];
                    $labelPrincipal = $row[$colLabel];

                    $labelAdicional = "";
                    $dataAttrs = "";
                    foreach ($extraCols as $colExtra) {
                        if (isset($row[$colExtra]) && !empty($row[$colExtra])) {
                            $valExtra = htmlspecialchars($row[$colExtra]);
                            $labelAdicional .= " ({$valExtra})";
                            $dataAttrs .= " data-{$colExtra}='{$valExtra}'";
                        }
                    }

                    $labelFinal = htmlspecialchars($labelPrincipal);
                    if (!empty($labelAdicional))
                        $labelFinal .= " " . $labelAdicional;
                    $html .= "<div class='custom-option' data-value='$val' $dataAttrs>$labelFinal</div>";
                    $count++;
                }

                if ($count >= $limit) {
                    $novoOffset = $offset + $limit;
                    $html .= "<div class='select-sentinel' 
                        data-tabela='$tabela' 
                        data-coluna='$colLabel' 
                        data-value-col='$colValue' 
                        data-offset='$novoOffset'
                        data-slug='$slug'
                        data-search='" . htmlspecialchars($searchTerm) . "'
                        data-nome-campo-origem='$nomeCampoOrigem'>Carregando mais...</div>";
                }

                echo $html;
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }
    }
}
