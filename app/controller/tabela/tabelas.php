<?php
class Tabelas
{
    private static $inforDate = [
        "agendamentos" => [
            "tabela" => "agendar_sala",
            "colunas" => ["id", "usuario", "sala", "bloco", "dia", "periodo"]
        ],
        "logins" => [
            "tabela" => "login",
            "colunas" => ["id", "usuario", "email", "senha"]
        ],
        "salas" => [
            "tabela" => "sala",
            "colunas" => ["id", "sala", "bloco", "tipo"]
        ]
    ];
    public static function geraTopTabela($tabela): string
    {
        if (empty($tabela)) return "Nenhuma tabela selecionada";
        $html = "<thead><tr>";
        $tabeleSelect = self::$inforDate[$tabela]['colunas'] ?? null;
        if ($tabeleSelect === null) return "Tabela não encontrada";
        foreach ($tabeleSelect as $coluna) {
            $html .= "<th>$coluna</th>";
        }
        return $html . "</tr></thead>";
    }
    public static function list_All($table)
    {
        $connect = Database::connects();
        $tmg = $connect->prepare("select * from $table");
        if (!$tmg->execute()) die("commad nao executado");
        return $tmg->get_result();
    }

    public static function geraBodyTabela($tabela): string
    {
        if (empty($tabela)) return "Nenhuma tabela selecionada";
        $listDate = Tabelas::list_All(self::$inforDate[$tabela]["tabela"]);
        $tabeleSelect = self::$inforDate[$tabela]["colunas"] ?? null;
        if ($tabeleSelect === null) return "Tabela não encontrada";
        $html = "<tbody>";
        while ($lina = $listDate->fetch_assoc()) {
            $html .= "<tr>";
            foreach ($tabeleSelect as $coluna) {
                $html .= "<td>" . $lina[$coluna] . "</td>";
            }
            $html .= "</tr>";
        }
        return $html . "</tbody>";
    }
}
