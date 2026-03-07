<?php
class menuTable
{
    private static $iconMenu;
    private static function iconMenu()
    {
        if (self::$iconMenu == null) {
            self::$iconMenu = require_once __DIR__ . "/arrayTableTop.php";
        }
    }
    public static function geraMenuTable($table, $previlegio): string
    {
        self::iconMenu();
        if (!isset(self::$iconMenu[$table])) return "não existe essa table";
        $acoesTabela = self::$iconMenu[$table][$previlegio] ?? '';
        if ($acoesTabela == null) return "Tabela não encontrada";
        $mapIcons = self::$iconMenu["acoes"];

        $html = "";
        foreach ($acoesTabela as $acao) {
            if (!isset($mapIcons[$acao])) continue;

            $icon = $mapIcons[$acao];

            $html .= "
            <svg class='icon-table'
                data-action='{$acao}'
                data-table='{$table}'>
                <use href='#{$icon}'></use>
            </svg>";
        }
        return $html;
    }
}
