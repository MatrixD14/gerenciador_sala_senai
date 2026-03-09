<?php
class Calendario
{
    public static function geraDia(): string
    {
        $html = "";
        for ($i = 0; $i < 35; $i++) {
            $html .= "<button class='dias-btn' date-dia='{$i}'>$i</button>";
        }
        return $html;
    }
}
