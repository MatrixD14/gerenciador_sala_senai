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
        if (!$config) return "<tr><td colspan='100%'>Configuração não encontrada</td></tr>";

        $tabelaPrincipal = $config["tabela"];
        $searchTerm = $_POST['search'] ?? null;
        $lastId = isset($_POST['last_id']) ? (int)$_POST['last_id'] : null;
        $limit = 50;
        $where = "";
        $condicoes = [];
        if ($UserLogin != null && $slug === 'menssagem') {
            $condicoes[] = "(revindicados.id_remetente = $UserLogin OR agendar_sala.idUser = $UserLogin)";
        }
        if ($lastId)
            $condicoes[] = "$tabelaPrincipal.id > $lastId";

        if ($searchTerm) {
            $filtros = [];
            $db = Database::connects();
            $termoOriginal = $db->real_escape_string($searchTerm);
            $termoData = $termoOriginal;
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{1,4})$/', $searchTerm, $matches)) {
                $termoData = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
            } elseif (preg_match('/^(\d{2})\/(\d{2})$/', $searchTerm, $matches)) {
                $termoData = "-{$matches[2]}-{$matches[1]}";
            } else {
                $termoData = str_replace('/', '', $termoOriginal);
            }

            if (!empty($config["especifico"])) {
                foreach ($config["especifico"] as $campo) {
                    $parts = explode(' as ', $campo);
                    $colunaFiltro = trim($parts[0]);
                    $termoParaSQL = (strpos($searchTerm, '/') !== false) ? $termoData : $termoOriginal;
                    $filtros[] = "$colunaFiltro LIKE '%$termoParaSQL%'";
                }
            } else {
                foreach ($config["colunas"] as $coluna => $prop) {
                    if (!empty($prop['encryption'])) continue;
                    if (($prop['type'] ?? '') === 'date') $filtros[] = "$coluna LIKE '%$termoData%'";
                    else $filtros[] = "$coluna LIKE '%$termoOriginal%'";
                }
            }
            $condicoes[] = "(" . implode(" OR ", $filtros) . ")";
        }
        $where = !empty($condicoes) ? " WHERE " . implode(" AND ", $condicoes) : "";
        $order = " ORDER BY $tabelaPrincipal.id ASC ";
        if (!empty($config["especifico"])) {
            $campos = implode(", ", $config["especifico"]);
            $joins = $config["join"] ?? "";
            $sql = "select $campos from $tabelaPrincipal $joins $where $order limit $limit";
        } else {
            $sql = "select * from $tabelaPrincipal $where $order limit $limit";
        }

        $listDate = Tabelas::list_All($sql);
        $html = "";
        $IdEncontrado = "";
        while ($linha = $listDate->fetch_assoc()) {
            $id = $linha['id'] ?? '';
            $IdEncontrado = $id;
            $displayRef = $linha['name'] ?? $linha['usuario'] ?? $linha['sala'] ?? '';

            $hoje = date('Y-m-d');
            $horaAtual = (int)date('H');
            $estaBloqueado = false;
            $dataDoRegistro = null;
            foreach ($config["colunas"] as $nomeCol => $prop) {
                if (($prop['type'] ?? '') === 'date') {
                    $colDataNome = $prop['maskname'] ?? $nomeCol;
                    if (strpos($colDataNome, ' as ') !== false) {
                        $parts = explode(' as ', $colDataNome);
                        $colDataNome = trim(end($parts));
                    }
                    $dataDoRegistro = $linha[$colDataNome] ?? null;
                    break;
                }
            }
            if ($dataDoRegistro && $dataDoRegistro < $hoje) {
                $estaBloqueado = true;
            } elseif ($dataDoRegistro === $hoje) {
                foreach ($config["colunas"] as $nomeCol => $prop) {
                    if (($prop['type'] ?? '') === 'select' && !empty($prop['options'])) {
                        $colReal = $nomeCol;
                        if (strpos($colReal, ' as ') !== false) {
                            $parts = explode(' as ', $colReal);
                            $colReal = trim(end($parts));
                        } elseif (strpos($colReal, '.') !== false) {
                            $parts = explode('.', $colReal);
                            $colReal = trim(end($parts));
                        }

                        $valorNoBanco = $linha[$colReal] ?? '';

                        if (isset($prop['options'][$valorNoBanco])) {
                            $regra = $prop['options'][$valorNoBanco];
                            if (is_array($regra)) {
                                $max = (int)($regra['max'] ?? 24);
                                if ($horaAtual >= $max) {
                                    $estaBloqueado = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $iconLock = $estaBloqueado ? "<span title='Fora do horário permitido ou data vencida'><svg class='icon'><use href='#icon-escramacao'></use></svg></span> " : "";

            $html .= "<tr data-id='$id' data-name='" . htmlspecialchars($displayRef) . "' class='" . ($estaBloqueado ? "row-locked" : "") . "'>";

            if (!empty($config["especifico"])) {
                $first = true;
                foreach ($linha as $valor) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}:\d{2})?$/', $valor)) {
                        $formato = (strpos($valor, ' ') !== false) ? 'd/m/Y H:i' : 'd/m/Y';
                        $valor = date($formato, strtotime($valor));
                    }
                    $conteudo = htmlspecialchars($valor ?? '');
                    if ($first) {
                        $conteudo = $iconLock . $conteudo;
                        $first = false;
                    }
                    $html .= "<td>" . $conteudo . "</td>";
                }
            } else {
                $first = true;
                foreach ($config["colunas"] as $coluna => $prop) {
                    if (!empty($prop['encryption'])) continue;
                    $valor = $linha[$coluna] ?? '';
                    if (($prop['type'] ?? '') === 'date' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                        if (!empty($valor)) {
                            $valor = date('d/m/Y', strtotime($valor));
                        }
                    }
                    $conteudo = htmlspecialchars($valor);
                    if ($first) {
                        $conteudo = $iconLock . $conteudo;
                        $first = false;
                    }
                    $html .= "<td>" . $conteudo . "</td>";
                }
            }
            $html .= "</tr>";
        }
        if ($IdEncontrado && $listDate->num_rows >= $limit) {
            $html .= "<tr class='sentinel' data-slug='$slug' data-lastid='$IdEncontrado' data-search='" . htmlspecialchars($searchTerm ?? '') . "'>
                <td colspan='100%' style='text-align:center;'>
                    Carregando mais registros...
                </td>
              </tr>";
        }

        return $html;
    }
}
