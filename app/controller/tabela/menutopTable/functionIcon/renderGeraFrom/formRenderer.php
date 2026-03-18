<?php

class FormRenderer
{
    public static function renderField($name, $col, $val, $isLocked, $allCols, $isHoje = false): string
    {
        $type = $col['type'] ?? 'text';
        $valEsc = htmlspecialchars($val);

        return match ($type) {
            'hidden' => "<input type='hidden' name='$name' value='$valEsc'>",

            'readonly_user' => "
                <input type='hidden' name='$name' value='$valEsc'>
                <input type='text' class='input-dados' value='{$_SESSION['nome']}' readonly>",

            'readonly' => "
                <input type='hidden' name='$name' value='$valEsc'>
                <input type='text' class='input-dados' value='" . self::resolveLabel($val, $col) . "' readonly style='cursor:not-allowed;'>",

            'date' => self::renderDate($name, $valEsc, $isLocked),

            'select' => self::renderSelect($name, $col, $val, $isLocked, $allCols, $isHoje),

            default => "<input type='$type' name='$name' id='$name' class='input-dados' value='$valEsc' " . ($isLocked ? "readonly style='cursor: not-allowed;'" : "") . ">"
        };
    }
    private static function renderDate($name, $val, $isLocked): string
    {
        $hoje = date('Y-m-d');
        $min = !$isLocked ? "min='$hoje'" : "";
        $ro = $isLocked ? "readonly style='cursor:not-allowed;'" : "";

        // Converte data de DD/MM/AAAA para YYYY-MM-DD se necessário
        if (!empty($val) && strpos($val, '/') !== false) {
            $p = explode('/', $val);
            $val = "{$p[2]}-{$p[1]}-{$p[0]}";
        }

        return "<input type='date' name='$name' id='$name' class='input-dados' value='$val' placeholder='dd/mm/aaaa' $min $ro>";
    }

    private static function renderSelect($name, $col, $selected, $isLocked, $allCols, $isHoje): string
    {
        if ($isLocked) {
            $label = self::resolveLabel($selected, $col);
            return "<input type='hidden' name='$name' value='$selected'>
                    <input type='text' class='input-dados' value='$label' readonly style='cursor:not-allowed;'>";
        }

        $html = "<select name='$name' id='$name' class='select-dados'>";
        $horaAtual = (int)date('H');

        // 1. Opções Estáticas (Regras de Horário)
        if (!empty($col['options'])) {
            foreach ($col['options'] as $optLabel => $regra) {
                $optVal = is_array($regra) ? $optLabel : $regra;
                $disabled = "";
                $labelExibicao = $optLabel;

                if (is_array($regra)) {
                    $min = $regra['min'] ?? 0;
                    $max = $regra['max'] ?? 24;
                    if ($isHoje && ($horaAtual < $min || $horaAtual >= $max)) {
                        $disabled = "disabled";
                        $labelExibicao .= " *";
                    }
                }

                $selAttr = ($optVal == $selected) ? "selected" : "";
                $html .= "<option value='$optVal' $selAttr $disabled>$labelExibicao</option>";
            }
        }

        // 2. Opções do Banco (Relações e Dependentes)
        if (!empty($col['relation'])) {
            $rel = $col['relation'];
            $deps = self::findDependents($name, $allCols);
            $extraSQL = $deps ? "," . implode(',', $deps) : "";

            $sql = "SELECT {$rel['value']}, {$rel['coluna']} $extraSQL FROM {$rel['tabela']}";
            $res = Database::connects()->query($sql);

            while ($row = $res->fetch_assoc()) {
                $dataAttrs = "";
                $labelExtras = [];
                foreach ($deps as $d) {
                    $valExtra = htmlspecialchars($row[$d]);
                    $dataAttrs .= " data-{$d}=\"{$valExtra}\"";
                    $labelExtras[] = "{$d}: {$valExtra}";
                }

                $textoExtra = !empty($labelExtras) ? " | " . implode(" - ", $labelExtras) : "";
                $sel = ($row[$rel['value']] == $selected) ? "selected" : "";

                $html .= "<option value='{$row[$rel['value']]}' $sel $dataAttrs>";
                $html .= htmlspecialchars($row[$rel['coluna']]) . $textoExtra;
                $html .= "</option>";
            }
        }

        $html .= "</select>";
        return $html;
    }
    private static function resolveLabel($val, $col)
    {
        if (!isset($col['relation']) || empty($col['relation'])) {
            return $val;
        }
        return RelationService::resolve($val, $col['relation'] ?? []);
    }

    private static function findDependents($masterName, $allCols)
    {
        $found = [];
        foreach ($allCols as $name => $conf) {
            if (($conf['depends'] ?? '') === $masterName) $found[] = $name;
        }
        return $found;
    }
}
