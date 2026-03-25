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
        if (!empty($config['especifico'])) {
            foreach ($config['especifico'] as $campo) {
                $parts = explode(' as ', $campo);
                $nomeExibicao = end($parts);

                if (strpos($nomeExibicao, '.') !== false) {
                    $nomeExibicao = explode('.', $nomeExibicao)[1];
                }

                $html .= "<th>" . ucfirst($nomeExibicao) . "</th>";
            }
        } else {
            foreach ($config['colunas'] as $coluna => $dados) {
                if (!empty($dados['encryption'])) continue;
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
        date_default_timezone_set('America/Sao_Paulo');
        self::loadConfig();
        $config = self::$inforDate[$slug] ?? null;

        if (!$config) {
            return "<tr><td colspan='100%'>Configuração não encontrada</td></tr>";
        }

        $limit = 50;
        $sql = self::buildQuery($slug, $config, $UserLogin, $limit);
        $listDate = Tabelas::list_All($sql);

        $html = "";
        $lastIdFound = "";

        while ($linha = $listDate->fetch_assoc()) {
            $id = $linha['id'] ?? '';
            $lastIdFound = $id;

            $estaBloqueado = self::checkIsLocked($linha, $config);

            $displayRef = $linha['name'] ?? $linha['usuario'] ?? $linha['sala'] ?? '';
            $iconLock = $estaBloqueado ? "<span title='Bloqueado: Fora do horário ou data vencida'><svg class='icon-escramacao'><use href='#icon-escramacao'></use></svg></span> " : "";
            $rowClass = $estaBloqueado ? "row-locked" : "";

            $html .= "<tr data-id='$id' data-name='" . htmlspecialchars($displayRef) . "' class='$rowClass'>";
            $first = true;
            $colunasParaLoop = !empty($config["especifico"]) ? $linha : $config["colunas"];

            foreach ($colunasParaLoop as $key => $val) {
                if (isset($_POST['show_cols']) && !in_array($key, $_POST['show_cols'])) {
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

        if ($lastIdFound && $listDate->num_rows >= $limit) {
            $searchTerm = $_POST['search'] ?? '';
            $html .= "<tr class='sentinel' data-slug='$slug' data-lastid='$lastIdFound' data-search='" . htmlspecialchars($searchTerm) . "'>
                    <td colspan='100%' style='text-align:center;'>Carregando mais registros...</td>
                  </tr>";
        }

        return $html;
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

    // private static function buildQuery($slug, $config, $UserLogin, $limit): string
    // {
    //     $tabelaPrincipal = $config["tabela"];
    //     $db = Database::connects();
    //     $condicoes = [];
    //     $lastId = isset($_POST['last_id']) ? (int)$_POST['last_id'] : null;
    //     $searchTerm = $_POST['search'] ?? null;

    //     if ($UserLogin != null && $slug === 'menssagem') {
    //         $condicoes[] = "(revindicados.id_remetente = $UserLogin OR agendar_sala.idUser = $UserLogin)";
    //     }
    //     if ($lastId) $condicoes[] = "$tabelaPrincipal.id > $lastId";

    //     if ($searchTerm) {
    //         $termoOriginal = $db->real_escape_string($searchTerm);
    //         $filtros = [];

    //         $termoData = (preg_match('/^\d{2}\/\d{2}/', $searchTerm)) ?
    //             implode('-', array_reverse(explode('/', $searchTerm))) :
    //             str_replace('/', '', $termoOriginal);

    //         $colunasParaFiltro = !empty($config["especifico"]) ? $config["especifico"] : array_keys($config["colunas"]);

    //         foreach ($colunasParaFiltro as $c) {
    //             $colFiltro = self::getCleanColumnName($c);
    //             $filtros[] = "$colFiltro LIKE '%$termoOriginal%'";
    //             if (strpos($searchTerm, '/') !== false) {
    //                 $filtros[] = "$colFiltro LIKE '%$termoData%'";
    //             }
    //         }
    //         $condicoes[] = "(" . implode(" OR ", $filtros) . ")";
    //     }

    //     $where = !empty($condicoes) ? " WHERE " . implode(" AND ", $condicoes) : "";
    //     $order = " ORDER BY $tabelaPrincipal.id ASC ";

    //     if (!empty($config["especifico"])) {
    //         $campos = implode(", ", $config["especifico"]);
    //         $joins = $config["join"] ?? "";
    //         return "SELECT $campos FROM $tabelaPrincipal $joins $where $order LIMIT $limit";
    //     }

    //     return "SELECT * FROM $tabelaPrincipal $where $order LIMIT $limit";
    // }
    private static function buildQuery($slug, $config, $UserLogin, $limit): string
    {
        $tabelaPrincipal = $config["tabela"];
        $db = Database::connects();
        $condicoes = [];
        $lastId = isset($_POST['last_id']) ? (int)$_POST['last_id'] : null;
        $searchTerm = $_POST['search'] ?? null;

        // 1. Filtros de Sistema (Seu código original)
        if ($UserLogin != null && $slug === 'menssagem') {
            $condicoes[] = "(revindicados.id_remetente = $UserLogin OR agendar_sala.idUser = $UserLogin)";
        }
        if ($lastId) $condicoes[] = "$tabelaPrincipal.id > $lastId";

        // 2. BUSCA GLOBAL (Input de texto geral)
        if ($searchTerm) {
            $termoOriginal = $db->real_escape_string($searchTerm);
            $filtros = [];
            $colunasParaFiltro = !empty($config["especifico"]) ? $config["especifico"] : array_keys($config["colunas"]);

            foreach ($colunasParaFiltro as $c) {
                $colFiltro = self::getCleanColumnName($c);
                $filtros[] = "$colFiltro LIKE '%$termoOriginal%'";
            }
            $condicoes[] = "(" . implode(" OR ", $filtros) . ")";
        }

        // 3. FILTROS DINÂMICOS (Checkboxes e Datas vindos do FiltroRenderer)
        foreach ($_POST as $key => $value) {
            // Ignora chaves que não são colunas do banco
            if (in_array($key, ['tabela', 'last_id', 'is_search_ajax', 'search', 'show_cols']) || empty($value)) continue;
            $configColuna = $config['colunas'][$key] ?? null;
            $colunaBanco = $configColuna['maskname'] ?? $key;
            $colunaLimpa = self::getCleanColumnName($colunaBanco);

            // Se a coluna for virtual, não filtramos direto no SQL (ou tratamos diferente)
            if (isset($configColuna['virtual']) && $configColuna['virtual']) continue;

            if (is_array($value)) {
                // Caso 1: Checkboxes (IN)
                $valoresLimpos = array_map(fn($v) => "'" . $db->real_escape_string($v) . "'", $value);
                $lista = implode(', ', $valoresLimpos);
                $condicoes[] = "$tabelaPrincipal.$colunaLimpa IN ($lista)";
            } elseif (str_ends_with($key, '_de')) {
                // Caso 2: Date Range
                $baseKey = str_replace('_de', '', $key);
                $ateKey = $baseKey . '_ate';
                $de = $db->real_escape_string($value);
                $ate = isset($_POST[$ateKey]) ? $db->real_escape_string($_POST[$ateKey]) : null;

                if ($de && $ate) {
                    $condicoes[] = "$tabelaPrincipal.$baseKey BETWEEN '$de' AND '$ate'";
                }
            } elseif (str_ends_with($key, '_ate')) {
                continue; // Pula pois já tratamos no '_de'
            } else {
                // Caso 3: Inputs de texto simples (LIKE)
                $val = $db->real_escape_string($value);
                $condicoes[] = "$tabelaPrincipal.$colunaLimpa LIKE '%$val%'";
            }
        }

        $where = !empty($condicoes) ? " WHERE " . implode(" AND ", $condicoes) : "";
        $order = " ORDER BY $tabelaPrincipal.id ASC ";

        if (!empty($config["especifico"])) {
            $campos = implode(", ", $config["especifico"]);
            $joins = $config["join"] ?? "";
            return "SELECT $campos FROM $tabelaPrincipal $joins $where $order LIMIT $limit";
        }

        return "SELECT * FROM $tabelaPrincipal $where $order LIMIT $limit";
    }
}
