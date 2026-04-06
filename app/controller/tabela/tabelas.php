<?php
class Tabelas
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require __DIR__ . '/arrayTables.php';
    }
    public static function log_error_table($log)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION["erro_table"] = $log;
    }
    public static function geraTopTabela($tabela): string
    {
        self::loadConfig();
        if (empty($tabela)) return "Nenhuma tabela selecionada";
        $config = self::$inforDate[$tabela] ?? null;
        if ($config === null) return "Tabela não encontrada";
        $html = "<tr>";
        $colunasVisiveis = $_POST['show_cols'] ?? null;
        if (is_string($colunasVisiveis)) {
            $decoded = json_decode($colunasVisiveis, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $colunasVisiveis = $decoded;
            } else {
                $colunasVisiveis = explode(',', $colunasVisiveis);
            }
        }
        if (!empty($config['especifico'])) {
            foreach ($config['especifico'] as $campo) {
                $parts = explode(' as ', $campo);
                $nomeExibicao = end($parts);

                if (strpos($nomeExibicao, '.') !== false) {
                    $nomeExibicao = explode('.', $nomeExibicao)[1];
                }
                $idColuna = self::getCleanColumnName($campo);
                if ($colunasVisiveis !== null && !in_array($idColuna, $colunasVisiveis)) {
                    continue;
                }

                $html .= "<th>" . ucfirst($nomeExibicao) . "</th>";
            }
        } else {
            foreach ($config['colunas'] as $coluna => $dados) {
                if (!empty($dados['encryption'])) continue;
                if ($colunasVisiveis !== null && !in_array($coluna, $colunasVisiveis)) {
                    continue;
                }
                $html .= "<th>" . ucfirst($coluna) . "</th>";
            }
        }
        return $html . "</tr>";
    }
    public static function list_All($table)
    {
        $connect = Database::connects();
        $tmg = $connect->prepare($table);
        if (!$tmg) {
            self::log_error_table("Erro na consulta SQL: " . $connect->error . " | Query: " . $table);
            return null;
        }
        if (!$tmg->execute()) die("commad nao executado");
        return $tmg->get_result();
    }

    public static function geraBodyTabela2($slug, $UserLogin = null): string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        date_default_timezone_set('America/Sao_Paulo');
        self::loadConfig();
        $config = self::$inforDate[$slug] ?? null;

        if (!$config) {
            return "<tr><td colspan='100%'>Configuração não encontrada</td></tr>";
        }
        $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
        $limit = 50;

        $sql = self::searchTabela($slug, $config, $UserLogin, $limit, $offset);
        $listDate = Tabelas::list_All($sql);

        $searchTerm = $_POST['search'] ?? '';
        if (!$listDate || $listDate->num_rows === 0) {
            if ($offset > 0) {
                return "<tr class='sentinel' data-slug='$slug' data-end='true'>
                        <td colspan='100%' style='text-align:center;'>Fim dos resultados</td>
                    </tr>";
            }
            $termo = htmlspecialchars($searchTerm);
            return "<tr><td colspan='100%'>Nenhum resultado encontrado para: <strong>$termo</strong></td></tr>";
        }

        $html = "";

        while ($linha = $listDate->fetch_assoc()) {
            $id = (int)($linha['id'] ?? 0);

            $estaBloqueado = self::checkIsLocked($linha, $config);

            $displayRef = $linha['name'] ?? $linha['usuario'] ?? $linha['sala'] ?? '';
            $iconLock = $estaBloqueado ? "<span title='Bloqueado: Fora do horário ou data vencida'><svg class='icon-escramacao'><use href='#icon-escramacao'></use></svg></span> " : "";
            $rowClass = $estaBloqueado ? "row-locked" : "";

            $html .= "<tr data-id='$id' data-name='" . htmlspecialchars($displayRef) . "' class='$rowClass'>";
            $colunasVisiveis = $_POST['show_cols'] ?? [];
            if (is_string($colunasVisiveis)) {
                $decoded = json_decode($colunasVisiveis, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $colunasVisiveis = $decoded;
                } else {
                    $colunasVisiveis = explode(',', $colunasVisiveis);
                }
            }
            if (!is_array($colunasVisiveis)) {
                $colunasVisiveis = [];
            }
            $colunasParaLoop = !empty($config["especifico"]) ? $linha : $config["colunas"];
            $first = true;

            foreach ($colunasParaLoop as $key => $val) {
                if (!empty($colunasVisiveis) && !in_array($key, $colunasVisiveis)) {
                    continue;
                }
                if (empty($config["especifico"])) {
                    if (!empty($val['encryption'])) continue;
                    $valorOriginal = $linha[$key] ?? '';
                    $tipo = $val['type'] ?? '';
                } else {
                    $valorOriginal = $val;
                    $tipo = 'auto';
                }

                $conteudo = self::formatCellValue($valorOriginal, $tipo);

                if ($first) {
                    $conteudo = $iconLock . $conteudo;
                    $first = false;
                }

                $html .= "<td>$conteudo</td>";
            }
            $html .= "</tr>";
        }
        $filtros = array_filter($_POST, function ($v) {
            return $v !== '' && $v !== null;
        });
        unset(
            $filtros['slug'],
            $filtros['offset'],
            $filtros['is_search_ajax'],
            $filtros['search']
        );
        $filtrosJson = htmlspecialchars(json_encode($filtros), ENT_QUOTES);

        $htmlTop = "";
        if ($offset > 0) {
            $prevOffset = max(0, $offset - $limit);
            $htmlTop = "<tr class='sentinel-top' data-offset='$prevOffset' data-slug='$slug' data-filtros='$filtrosJson'>
                        <td colspan='100%' style='text-align:center; font-size: 0.8em; color: #ccc;'>Carregando anteriores...</td>
                    </tr>";
        } else $htmlTop = "";
        $htmlBottom = "";
        if ($listDate->num_rows >= $limit) {
            $proximoOffset = $offset + $limit;
            $htmlBottom = "<tr class='sentinel' data-offset='$proximoOffset' data-slug='$slug' data-search='" . htmlspecialchars($searchTerm) . "' data-filtros='$filtrosJson'>
                        <td colspan='100%' style='text-align:center;'>Carregando mais...</td>
                      </tr>";
        } else {
            $htmlBottom = "<tr class='sentinel' data-slug='$slug' data-end='true'>
                        <td colspan='100%' style='text-align:center;'>Fim dos resultados</td>
                       </tr>";
        }
        error_log("📥 offset recebido: " . ($_POST['offset'] ?? 'null'));
        error_log("🔍 sentinela inferior offset: " . ($proximoOffset ?? 'nenhum'));
        error_log("POST recebido: " . print_r($_POST, true));
        return $htmlTop . $html . $htmlBottom;
    }
    private static function checkIsLocked($linha, $config): bool
    {
        $hoje = date('Y-m-d');
        $horaAtual = (int)date('H');
        $dataDoRegistro = null;

        foreach ($config["colunas"] as $nomeCol => $prop) {
            if (($prop['type'] ?? '') === 'date') {
                $colReal = self::getCleanColumnName($prop['maskname'] ?? $nomeCol);
                $dataDoRegistro = $linha[$colReal] ?? null;
                break;
            }
        }
        if ($dataDoRegistro && $dataDoRegistro < $hoje) return true;
        if ($dataDoRegistro === $hoje) {
            foreach ($config["colunas"] as $nomeCol => $prop) {
                if (($prop['type'] ?? '') === 'select' && !empty($prop['options'])) {
                    $colReal = self::getCleanColumnName($nomeCol);
                    $valorNoBanco = $linha[$colReal] ?? '';

                    if (isset($prop['options'][$valorNoBanco])) {
                        $regra = $prop['options'][$valorNoBanco];
                        $max = (int)($regra['max'] ?? 24);
                        if ($horaAtual >= $max) return true;
                    }
                }
            }
        }

        return false;
    }

    private static function getCleanColumnName($nome): string
    {
        if (strpos($nome, ' as ') !== false) {
            $parts = explode(' as ', $nome);
            return trim(end($parts));
        }
        if (strpos($nome, '.') !== false) {
            $parts = explode('.', $nome);
            return trim(end($parts));
        }
        return $nome;
    }

    private static function formatCellValue($valor, $tipo): string
    {
        if (empty($valor)) return "";
        if ($tipo === 'date' || preg_match('/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}:\d{2})?$/', $valor)) {
            $temHora = (strpos($valor, ' ') !== false);
            $formato = $temHora ? 'd/m/Y H:i' : 'd/m/Y';
            return date($formato, strtotime($valor));
        }

        return htmlspecialchars($valor);
    }

    private static function applyCustomFilters($slug, $db, $tabelaPrincipal): array
    {
        $condicoes = [];
        $filtroConfigPath = __DIR__ . '/menutopTable/functionIcon/filtrotabele/arrayTabelaFiltro.php';

        if (!file_exists($filtroConfigPath)) return [];

        $allFiltros = require $filtroConfigPath;
        $colsFiltro = $allFiltros[$slug]['colunas'] ?? [];

        foreach ($colsFiltro as $nomeCampo => $info) {
            if ($info['type'] === 'date-range') {
                $de = $_POST["{$nomeCampo}_de"] ?? null;
                $ate = $_POST["{$nomeCampo}_ate"] ?? null;

                if (!empty($de)) {
                    $de = $db->real_escape_string($de);
                    $condicoes[] = "$tabelaPrincipal.$nomeCampo >= '$de'";
                }
                if (!empty($ate)) {
                    $ate = $db->real_escape_string($ate);
                    $condicoes[] = "$tabelaPrincipal.$nomeCampo <= '$ate'";
                }
            } else {
                $valor = $_POST[$nomeCampo] ?? null;
                if ($valor !== null && $valor !== '') {
                    $valorLimpo = $db->real_escape_string($valor);
                    if (isset($info['relation']['tabela'])) {
                        $tabelaRelacao = $info['relation']['tabela'];
                        $colunaRelacao = $info['relation']['value'] ?? $info['relation']['coluna'];
                        $condicoes[] = "$tabelaRelacao.$colunaRelacao = '$valorLimpo'";
                    } else
                        $condicoes[] = "$tabelaPrincipal.$nomeCampo = '$valorLimpo'";
                }
            }
        }

        return $condicoes;
    }

    private static function applyOrder($slug, $config, $tabelaPrincipal): string
    {
        $orderBy = $_POST['order_by'] ?? 'id';
        $direction = ($_POST['order_direction'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $filtroConfigPath = __DIR__ . '/menutopTable/functionIcon/filtrotabele/arrayTabelaFiltro.php';
        $allFiltros = file_exists($filtroConfigPath) ? require $filtroConfigPath : [];
        $colunasPermitidas = $allFiltros[$slug]['orderna'] ?? ['id'];

        if (in_array($orderBy, $colunasPermitidas)) {
            if ($orderBy === 'id')
                $orderBy = " $tabelaPrincipal.id";

            return " ORDER BY $orderBy $direction ";
        }

        return " ORDER BY $tabelaPrincipal.id ASC ";
    }

    private static function searchTabela($slug, $config, $UserLogin, $limit, $offset): string
    {
        $tabelaPrincipal = $config["tabela"];
        $db = Database::connects();
        $condicoes = [];

        $filtrosCustom = self::applyCustomFilters($slug, $db, $tabelaPrincipal);
        if (!empty($filtrosCustom)) $condicoes = array_merge($condicoes, $filtrosCustom);

        $searchTerm = $_POST['search'] ?? '';


        if ($UserLogin != null && $slug === 'menssagem') {
            $condicoes[] = "(revindicados.id_remetente = $UserLogin OR agendar_sala.idUser = $UserLogin)";
        }

        $searchCond = searchTabelas::buildSearch($slug, $searchTerm, $tabelaPrincipal);

        if (!empty($searchCond)) {
            $condicoes = array_merge($condicoes, $searchCond);
        }

        $where = !empty($condicoes) ? " WHERE " . implode(" AND ", $condicoes) : "";
        $order = self::applyOrder($slug, $config, $tabelaPrincipal);
        $joins = $config["join"] ?? "";

        $select = !empty($config["especifico"]) ? implode(", ", $config["especifico"]) : "$tabelaPrincipal.*";
        $query = "SELECT $select FROM $tabelaPrincipal $joins $where $order LIMIT $offset, $limit";
        error_log("QUERY GERADA: " . $query);
        return $query;
    }
}
