<?php
class editor
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require __DIR__ . '/../../arrayTables.php';
    }
    public static function geraeditor($table): string
    {
        self::loadConfig();
        if (empty($table)) return "Nenhuma tabela selecionada";
        $html = "";
        $tabeleSelect = self::$inforDate[$table]['colunas'] ?? null;
        if ($tabeleSelect === null) return "Tabela não encontrada";
        foreach ($tabeleSelect as $coluna => $tipo) {
            if ($coluna === "id") continue;
            $html .= "<label for='$coluna'>$coluna</label><br>";
            if (is_array($tipo)) {

                $html .= "<select name='$coluna' id='$coluna'>";

                foreach ($tipo as $valor) {
                    $html .= "<option value='$valor'>$valor</option>";
                }

                $html .= "</select>";
            } else {

                $html .= "<input type='$tipo' name='$coluna' id='$coluna'>";
            }

            $html .= "<br><br>";
        }
        return $html;
    }
}
