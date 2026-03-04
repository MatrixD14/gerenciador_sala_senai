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
        $icons = self::$iconMenu[$table][$previlegio];
        if ($icons == null) return "Tabela não encontrada";
        $addIcon = "";
        foreach ($icons as $listIcon) {
            $addIcon .= "<svg class='icon-table' data-action='{$listIcon}' data-table='{$table}'><use href='#$listIcon'></use></svg>";
        }
        return $addIcon;
    }
}
