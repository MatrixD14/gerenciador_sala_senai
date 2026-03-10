<?php
class gerarFromDinamico
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require_once __DIR__ . '/../../arrayTables.php';
    }
    public static function geraFrom($table, $id = null): string
    {
        self::loadConfig();
        $config =  self::$inforDate[$table] ?? null;
        if (!$config) return "Configuração não encontrada";
        $dados = [];
        $isRegistroPassado = false;
        $hoje = date('Y-m-d');
        if ($id !== null) {
            $tabela = $config['tabela'] ?? null;
            $db = Database::connects();
            $stmt = $db->prepare("select * from $tabela WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $dados = $result->fetch_assoc() ?? [];
            if (isset($dados['dia']) && $dados['dia'] < $hoje) {
                $isRegistroPassado = true;
            }
        }
        $coluneSelect = $config['colunas'] ?? null;
        $html = "";
        foreach ($coluneSelect as $coluna => $config) {

            if (!empty($config['primary'])) continue;
            $tipo = $config['type'] ?? 'text';
            $campoBanco = $config['maskname'] ?? $coluna;
            $valorBanco = $dados[$campoBanco] ?? '';
            $valorEscapado = htmlspecialchars($valorBanco);

            if ($tipo !== 'hidden') {
                $html .= "<label for='$coluna'>" . ucfirst($coluna) . "</label><br>";
            }
            $readonlyAttr = $isRegistroPassado ? "readonly style='cursor: not-allowed;'" : "";
            if ($tipo === 'date') {
                $minAttr = !$isRegistroPassado ? "min='$hoje'" : "";
                $html .= "<input type='date' name='$coluna' id='$coluna' class='input-dados' value='$valorEscapado' $readonlyAttr $minAttr>";
            } elseif ($tipo === "readonly") {
                $valor = htmlspecialchars($valorBanco);

                $html .= "<input type='text' id='$coluna' value='$valor' readonly>";
            } elseif ($tipo === "select") {

                if ($isRegistroPassado) {
                    $mostrarValor = $valorEscapado;
                    if (!empty($config['relation'])) {
                        $rel = $config['relation'];
                        $db = Database::connects();
                        $sqlRel = "select {$rel['coluna']} from {$rel['tabela']} where {$rel['value']} = ?";
                        $stmtRel = $db->prepare($sqlRel);
                        $stmtRel->bind_param("s", $valorBanco);
                        $stmtRel->execute();
                        $resRel = $stmtRel->get_result()->fetch_assoc();

                        if ($resRel) {
                            $mostrarValor = htmlspecialchars($resRel[$rel['coluna']]);
                        }
                    }

                    $html .= "<input type='hidden' name='$coluna' value='$valorEscapado'>";
                    $html .= "<input type='text' id='display_$coluna' class='input-dados' value='$mostrarValor' readonly style='cursor: not-allowed;''>";
                } else {
                    $html .= "<select class='select-dados' name='$coluna' id='$coluna'>";

                    // opções fixas
                    if (!empty($config['options'])) {
                        foreach ($config['options'] as $opt) {
                            $selected = ($opt == $valorBanco) ? "selected" : "";
                            $html .= "<option value='$opt' $selected>$opt</option>";
                        }
                    }

                    // relação com outra tabela
                    if (!empty($config['relation'])) {
                        $rel = $config['relation'];
                        $camposExtras = [];
                        foreach ($coluneSelect as $c => $conf) {
                            if (!empty($conf['depends']) && $conf['depends'] == $coluna) {
                                $camposExtras[] = $c;
                            }
                        }

                        $extraSQL = $camposExtras ? "," . implode(",", $camposExtras) : "";
                        $sql = "select {$rel['value']}, {$rel['coluna']} $extraSQL from {$rel['tabela']}";
                        $lista = Tabelas::list_All($sql);

                        while ($row = $lista->fetch_assoc()) {
                            $selected = ($row[$rel['value']] == $valorBanco) ? "selected" : "";
                            $dataAttributes = "";
                            $labelExtras = [];
                            foreach ($camposExtras as $campoExtra) {
                                if (isset($row[$campoExtra])) {
                                    $valExtra = htmlspecialchars($row[$campoExtra]);
                                    $dataAttributes .= " data-{$campoExtra}=\"{$valExtra}\"";
                                    $labelExtras[] = "{$campoExtra}: {$valExtra}";
                                }
                            }

                            $textoExtra = !empty($labelExtras) ? " | " . implode(" - ", $labelExtras) : "";
                            $html .= "<option value='{$row[$rel['value']]}' $selected $dataAttributes>";
                            $html .= htmlspecialchars($row[$rel['coluna']]) . $textoExtra;
                            $html .= "</option>";
                        }
                    }
                    $html .= "</select>";
                }
            } else {
                $html .= "<input type='$tipo' name='$coluna' id='$coluna' class='input-dados' value='$valorEscapado' $readonlyAttr>";
            }

            if ($tipo !== 'hidden') $html .= "<br><br>";
        }
        return $html;
    }
}
