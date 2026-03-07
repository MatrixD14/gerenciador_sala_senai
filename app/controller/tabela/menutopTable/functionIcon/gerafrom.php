<?php
class gerarFromDinamico
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require_once __DIR__ . '/../../arrayTables.php';
    }
    public static function geraFrom($table, $id): string
    {
        self::loadConfig();
        $config =  self::$inforDate[$table] ?? null;
        if (!$config) return "Configuração não encontrada";
        $tabela = $config['tabela'] ?? null;
        $coluneSelect = $config['colunas'] ?? null;
        $db = Database::connects();
        $stmt = $db->prepare("SELECT * FROM $tabela WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dados = $result->fetch_assoc() ?? [];

        $html = "";
        foreach ($coluneSelect as $coluna => $config) {

            if (!empty($config['primary'])) continue;
            $tipo = $config['type'] ?? 'text';
            $campoBanco = $config['maskname'] ?? $coluna;
            $valorBanco = $dados[$campoBanco] ?? '';

            if ($tipo !== 'hidden') {
                $label = ucfirst($coluna);
                $html .= "<label for='$coluna'>$label</label><br>";
            }
            if ($tipo === "readonly") {

                $valor = htmlspecialchars($valorBanco);

                $html .= "<input type='text' id='$coluna' value='$valor' readonly>";
            } elseif ($tipo === "select") {

                $html .= "<select name='$coluna' id='$coluna'>";

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
            } else {

                $valor = htmlspecialchars($valorBanco);

                $html .= "<input type='$tipo' name='$coluna' id='$coluna' value='$valor'>";
            }

            if ($tipo !== 'hidden') $html .= "<br><br>";
        }
        return $html;
    }
}
