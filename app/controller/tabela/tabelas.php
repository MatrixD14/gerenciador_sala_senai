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
        if (!$tmg->execute()) die("commad nao executado");
        return $tmg->get_result();
    }

    public static function geraBodyTabela2($slug): string
    {
        self::loadConfig();
        $config = self::$inforDate[$slug] ?? null;
        if (!$config) return "<tr><td colspan='100%'>Configuração não encontrada</td></tr>";

        $tabelaPrincipal = $config["tabela"];
        $searchTerm = $_POST['search'] ?? null;
        $lastId = isset($_POST['last_id']) ? (int)$_POST['last_id'] : null;
        $limit = 50;
        $where = "";
        $condicoes = [];
        if ($lastId)
            $condicoes[] = "$tabelaPrincipal.id > $lastId";

        if ($searchTerm) {
            $filtros = [];
            if (!empty($config["especifico"])) {
                foreach ($config["especifico"] as $campo) {
                    $parts = explode(' as ', $campo);
                    $colunaFiltro = trim($parts[0]);
                    $filtros[] = "$colunaFiltro LIKE '%$searchTerm%'";
                }
            } else {
                foreach ($config["colunas"] as $coluna => $prop) {
                    if (!empty($prop['encryption'])) continue;
                    $filtros[] = "$coluna LIKE '%$searchTerm%'";
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

            $html .= "<tr data-id='$id' data-name='" . htmlspecialchars($displayRef) . "'>";

            if (!empty($config["especifico"])) {
                foreach ($linha as $valor) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                        $valor = date('d/m/Y', strtotime($valor));
                    }
                    $html .= "<td>" . htmlspecialchars($valor ?? '') . "</td>";
                }
            } else {
                foreach ($config["colunas"] as $coluna => $prop) {
                    if (!empty($prop['encryption'])) continue;
                    $valor = $linha[$coluna] ?? '';
                    if (($prop['type'] ?? '') === 'date' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
                        if (!empty($valor)) {
                            $valor = date('d/m/Y', strtotime($valor));
                        }
                    }
                    $html .= "<td>" . htmlspecialchars($linha[$coluna] ?? '') . "</td>";
                }
            }
            $html .= "</tr>";
        }
        if ($IdEncontrado && $listDate->num_rows >= $limit) {
            $html .= "<tr class='sentinel' data-slug='$slug' data-lastid='$IdEncontrado'>
                <td colspan='100%' style='text-align:center;'>
                    Carregando mais registros...
                </td>
              </tr>";
        }

        return $html;
    }
}
