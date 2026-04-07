<?php
class searchTabelas
{
    private static $arrayTabela;
    private static function loadConfig()
    {
        if (!self::$arrayTabela)
            self::$arrayTabela = require __DIR__ .  "/arrayTabelasearch.php";
    }

    public static function buildSearch($slug, $searchTerm, $tabelaPrincipal)
    {
        self::loadConfig();
        $db = Database::connects();

        $colunasBusca = self::$arrayTabela[$slug]['searchable'] ?? [];

        if (!$searchTerm || empty($colunasBusca)) {
            return [];
        }

        $termo = $db->real_escape_string($searchTerm);
        $filtros = [];

        foreach ($colunasBusca as $campo) {

            if (strpos($campo, '.') === false) {
                $campo = "$tabelaPrincipal.$campo";
            }

            $filtros[] = "$campo LIKE '%$termo%'";
        }

        return ["(" . implode(" OR ", $filtros) . ")"];
    }
}
