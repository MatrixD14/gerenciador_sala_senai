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

    public static function geraBodyTabela2($slug, $UserLogin = null)
    {
        self::loadConfig();
        $config = self::$inforDate[$slug] ?? null;
        if (!$config) return json_encode(['erro' => 'Config não encontrada']);
        $configExibicao = $config;
        $colunasSolicitadas = $_POST['show_cols'] ?? null;
        $colunasConfiguradas = $config['colunas'] ?? null;

        if ($colunasSolicitadas && is_array($colunasSolicitadas) && $colunasConfiguradas) {
            $novasColunas = [];
            foreach ($colunasConfiguradas as $colNome => $prop) {
                // foreach ($colunasSolicitadas as $colNome) {
                // if (isset($colunasConfiguradas[$colNome])) {
                //     $novasColunas[$colNome] = $colunasConfiguradas[$colNome];
                // }
                if (in_array($colNome, $colunasSolicitadas) || ($prop['ghost'] ?? false)) {
                    $novasColunas[$colNome] = $prop;
                }
            }
            $configExibicao['colunas'] = $novasColunas;
        }

        $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
        $limit = 50;

        $sql = self::searchTabela($slug, $configExibicao, $UserLogin, $limit, $offset);
        $result = Tabelas::list_All($sql);

        $dados = [];
        while ($linha = $result->fetch_assoc()) {
            // O PHP só faz o cálculo lógico, sem HTML
            $linha['is_locked'] = self::checkIsLocked($linha, $configExibicao);
            $dados[] = $linha;
        }
        $has_more = ($result->num_rows === $limit);
        // Retorna JSON puro para o JS trabalhar
        header('Content-Type: application/json');
        echo json_encode([
            'dados' => $dados,
            'config' => [
                'especifico' => $configExibicao['especifico'] ?? null,
                'colunas' => $configExibicao['colunas'] ?? null,
                'colunas_visiveis' => $_POST['show_cols'] ?? null
            ],
            'offset' => $offset,
            'limit' => $limit,
            'has_more' => $has_more
        ]);
        exit;
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
        $nomeLower = strtolower($nome);
        if (strpos($nomeLower, ' as ') !== false) {
            $parts = explode(' as ', $nome);
            return trim(end($parts));
        }
        if (strpos($nome, '.') !== false) {
            $parts = explode('.', $nome);
            return trim(end($parts));
        }
        return $nome;
    }

    private static function getSelectAliasMap($config): array
    {
        $map = [];
        if (!empty($config['especifico'])) {
            foreach ($config['especifico'] as $expr) {
                if (stripos($expr, ' as ') !== false) {
                    $parts = preg_split('/\s+as\s+/i', $expr);
                    $alias = trim(end($parts));
                    $expression = trim($parts[0]);
                } else {
                    $alias = self::getCleanColumnName($expr);
                    $expression = $expr;
                }
                $map[$alias] = $expression;
            }
        } else {
            $tabelaPrincipal = $config['tabela'];
            foreach ($config['colunas'] as $col => $info) {
                $colunaReal = $info['maskname'] ?? $col;
                $map[$col] = "$tabelaPrincipal.$colunaReal";
            }
        }
        return $map;
    }

    private static function applyCustomFilters($slug, $db, $tabelaPrincipal, $aliasMap): array
    {
        $condicoes = [];
        $filtroConfigPath = __DIR__ . '/menutopTable/functionIcon/filtrotabele/arrayTabelaFiltro.php';
        if (!file_exists($filtroConfigPath)) return [];

        $allFiltros = require $filtroConfigPath;
        $colsFiltro = $allFiltros[$slug]['colunas'] ?? [];
        error_log("Verificando existência do arquivo de filtros em: " . json_encode($_POST));
        error_log("POST RECEBIDO: " . print_r($_POST, true));

        foreach ($colsFiltro as $nomeCampo => $info) {
            $expressaoSQL = $aliasMap[$nomeCampo] ?? "$tabelaPrincipal.$nomeCampo";
            if ($info['type'] === 'date-range') {
                $de = $_POST["{$nomeCampo}_de"] ?? null;
                $ate = $_POST["{$nomeCampo}_ate"] ?? null;

                if (!empty($de)) {
                    $de = $db->real_escape_string($de);
                    $condicoes[] = "$expressaoSQL >= '$de'";
                }
                if (!empty($ate)) {
                    $ate = $db->real_escape_string($ate);
                    $condicoes[] = "$expressaoSQL <= '$ate'";
                }
            } else {
                $valor = $_POST[$nomeCampo] ?? null;
                if ($valor !== null && $valor !== '') {
                    $valorLimpo = $db->real_escape_string($valor);
                    if (isset($info['relation']['tabela'])) {
                        $tabelaRelacao = $info['relation']['tabela'];
                        $colunaRelacao = $info['relation']['value'] ?? $info['relation']['coluna'];
                        $condicoes[] = "$tabelaRelacao.$colunaRelacao = '$valorLimpo'";
                    } else {
                        $condicoes[] = "$expressaoSQL = '$valorLimpo'";
                    }
                }
            }
            error_log("Processando campo: $nomeCampo, valor: " . ($_POST[$nomeCampo] ?? 'NULL'));
        }

        return $condicoes;
    }

    private static function applyOrder($slug, $config, $tabelaPrincipal, $aliasMap): string
    {
        $orderBy = $_POST['order_by'] ?? 'id';
        $direction = ($_POST['order_direction'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
        $filtroConfigPath = __DIR__ . '/menutopTable/functionIcon/filtrotabele/arrayTabelaFiltro.php';
        $allFiltros = file_exists($filtroConfigPath) ? require $filtroConfigPath : [];
        $colunasPermitidas = $allFiltros[$slug]['orderna'] ?? ['id'];

        if (in_array($orderBy, $colunasPermitidas)) {
            $orderByExpr = $aliasMap[$orderBy] ?? "$tabelaPrincipal.$orderBy";
            return " ORDER BY $orderByExpr $direction ";
        }

        return " ORDER BY $tabelaPrincipal.id ASC ";
    }

    private static function searchTabela($slug, $config, $UserLogin, $limit, $offset): string
    {
        $tabelaPrincipal = $config["tabela"];
        $db = Database::connects();
        $condicoes = [];

        $aliasMap = self::getSelectAliasMap($config);
        $filtrosCustom = self::applyCustomFilters($slug, $db, $tabelaPrincipal, $aliasMap);
        if (!empty($filtrosCustom)) $condicoes = array_merge($condicoes, $filtrosCustom);

        $searchTerm = $_POST['search'] ?? '';


        if ($UserLogin != null && $slug === 'menssagem') {
            $condicoes[] = "(requisicoes_troca.id_remetente = $UserLogin OR agendar_sala.idUser = $UserLogin)";
        }

        $searchCond = searchTabelas::buildSearch($slug, $searchTerm, $tabelaPrincipal);

        if (!empty($searchCond)) {
            $condicoes = array_merge($condicoes, $searchCond);
        }

        $where = !empty($condicoes) ? " WHERE " . implode(" AND ", $condicoes) : "";
        $order = self::applyOrder($slug, $config, $tabelaPrincipal, $aliasMap);
        $joins = $config["join"] ?? "";

        $select = !empty($config["especifico"]) ? implode(", ", $config["especifico"]) : "$tabelaPrincipal.*";
        $query = "SELECT $select FROM $tabelaPrincipal $joins $where $order LIMIT $offset, $limit";
        error_log("QUERY GERADA: " . $query);
        return $query;
    }
}
