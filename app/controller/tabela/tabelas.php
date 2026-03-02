<?php
class Tabelas
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require __DIR__ . '/arrayTables.php';
    }
    public static function geraTopTabela($tabela): string
    {
        self::loadConfig();
        if (empty($tabela)) return "Nenhuma tabela selecionada";
        $html = "<tr>";
        $tabeleSelect = self::$inforDate[$tabela]['colunas'] ?? null;
        if ($tabeleSelect === null) return "Tabela não encontrada";
        foreach ($tabeleSelect as $coluna) {
            $html .= "<th>$coluna</th>";
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

    public static function geraBodyTabela($tabela): string
    {
        self::loadConfig();
        if (!isset(self::$inforDate[$tabela]))
            return "Tabela não encontrada";

        $listDate = Tabelas::list_All("select * from " . self::$inforDate[$tabela]["tabela"]);
        $tabeleSelect = self::$inforDate[$tabela]["colunas"] ?? null;
        if ($tabeleSelect === null) return "Tabela não encontrada";
        $html = "";
        while ($lina = $listDate->fetch_assoc()) {
            $html .= "<tr>";
            foreach ($tabeleSelect as $coluna) {
                $html .= "<td>" . $lina[$coluna] . "</td>";
            }
            $html .= "</tr>";
        }
        return $html;
    }
    public static function geraBodyJoinTabela($tabela): string
    {
        self::loadConfig();
        if (!isset(self::$inforDate[$tabela]))
            return "Tabela não encontrada";

        $config = self::$inforDate[$tabela];
        $nomeTabela = $config["tabela"];
        $joins = $config["join"];
        $especificos = implode(", ", $config["especifico"]);
        $sql = "select $especificos from $nomeTabela $joins";
        $listDate = Tabelas::list_All($sql);
        $html = "";
        while ($lina = $listDate->fetch_assoc()) {
            $html .= "<tr>";
            foreach ($lina as $valor) {
                $html .= "<td>" . htmlspecialchars($valor ?? '') . "</td>";
            }
            $html .= "</tr>";
        }
        return $html;
    }
}
