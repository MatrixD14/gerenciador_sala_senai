<?php

class FormRenderer
{
    public static function renderField($name, $col, $val, $isLocked, $allCols, $isHoje = false, $slug = ''): string
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

            'select' => self::renderSelect($name, $col, $val, $isLocked, $allCols, $isHoje, $slug),

            default => "<input type='$type' name='$name' id='$name' class='input-dados' value='$valEsc' " . ($isLocked ? "readonly style='cursor: not-allowed;'" : "") . ">"
        };
    }
    private static function renderDate($name, $val, $isLocked): string
    {
        $hoje = date('Y-m-d');
        $min = !$isLocked ? "min='$hoje'" : "";
        $ro = $isLocked ? "readonly style='cursor:not-allowed;'" : "";

        if (!empty($val) && strpos($val, '/') !== false) {
            $p = explode('/', $val);
            $val = "{$p[2]}-{$p[1]}-{$p[0]}";
        }

        return "<input type='date' name='$name' id='$name' class='input-dados' value='$val' placeholder='dd/mm/aaaa' $min $ro>";
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

    private static function renderSelect($name, $col, $selected, $isLocked, $allCols, $isHoje, $slug = ''): string
    {
        $horaAtual = (int)date('H');
        $rel = $col['relation'] ?? null;
        $deps = self::findDependents($name, $allCols);

        $displayLabel = self::getSelectDisplayLabel($name, $col, $selected, $deps);
        if ($isLocked) {
            return "<input type='hidden' name='$name' value='$selected'>
                <input type='text' class='input-dados' value='$displayLabel' readonly style='cursor:not-allowed;'>";
        }

        $relDataAttrs = $rel ? "data-tabela='{$rel['tabela']}' data-coluna='{$rel['coluna']}' data-value-col='{$rel['value']}' data-slug='$slug' data-nome-campo-origem='$name'" : "";
        $html = "<div class='custom-select-container' id='container-$name' $relDataAttrs>
                <input type='hidden' name='$name' id='hidden-$name' value='$selected'>
                <input type='text' class='custom-select-trigger select-dados' id='$name' value='$displayLabel' readonly autocomplete='off' style='cursor: pointer;'>
                <div class='custom-select-options'>
                    <input class='custom-search input-dados' name='search-$name' id='search-$name' placeholder='digite dois caracteres...' autofocus>
                    <div class='selected-item-top-container'>";

        if (!empty($selected) && $displayLabel !== "Selecione...") {
            $html .= "<div class='selected-badge'><small>selecionado antes*:</small>
                    <div class='custom-option selected' data-value='$selected'>$displayLabel</div>
                  </div>";
        }

        $html .= "</div><div class='options-scroll-area'>";
        $html .= "<div class='custom-option default-option' data-value='' >Selecione...</div>";

        $html .= self::renderStaticOptions($col, $isHoje, $horaAtual);

        if ($rel) {
            $html .= self::renderDatabaseOptions($rel, $deps, $slug, $name);
        }

        $html .= "</div></div></div>";
        return $html;
    }

    private static function getSelectDisplayLabel($name, $col, $selected, $deps): string
    {
        if (empty($selected)) return "Selecione...";

        $rel = $col['relation'] ?? null;
        if ($rel) {
            $extraCols = !empty($deps) ? ", " . implode(',', $deps) : "";
            $sql = "SELECT {$rel['coluna']} $extraCols FROM {$rel['tabela']} WHERE {$rel['value']} = ?";
            $stmt = Database::connects()->prepare($sql);
            $stmt->bind_param("s", $selected);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $labels = [$row[$rel['coluna']]];
                foreach ($deps as $d) {
                    if (!empty($row[$d])) $labels[] = $row[$d];
                }
                $textoExtra = count($labels) > 1 ? " (" . implode(" - ", array_slice($labels, 1)) . ")" : "";
                return htmlspecialchars($labels[0] . $textoExtra);
            }
        }
        if (!empty($col['options'])) {
            foreach ($col['options'] as $key => $regra) {
                $label = is_array($regra) ? $key : (is_numeric($key) ? $regra : $key);
                $val   = is_array($regra) ? $key : (is_numeric($key) ? $regra : $regra);
                if ($val == $selected) return $label;
            }
        }

        return "Selecione...";
    }

    private static function renderStaticOptions($col, $isHoje, $horaAtual): string
    {
        if (empty($col['options'])) return "";
        $html = "";
        foreach ($col['options'] as $key => $regra) {
            $optLabel = is_numeric($key) ? $regra : $key;
            $optVal   = is_array($regra) ? $optLabel : (is_numeric($key) ? $regra : $regra);

            $classDisabled = "";
            $labelExibicao = htmlspecialchars($optLabel);
            if (is_array($regra)) {
                $min = $regra['min'] ?? 0;
                $max = $regra['max'] ?? 24;
                if ($isHoje && ($horaAtual < $min || $horaAtual >= $max)) {
                    $classDisabled = "option-disabled";
                    $labelExibicao .= " *";
                }
            }
            $html .= "<div class='custom-option $classDisabled' data-value='$optVal'>$labelExibicao</div>";
        }
        return $html;
    }

    private static function renderDatabaseOptions($rel, $deps, $slug, $name): string
    {
        $extraSQL = !empty($deps) ? ", " . implode(', ', array_filter($deps)) : "";
        $sql = "SELECT DISTINCT {$rel['value']}, {$rel['coluna']} $extraSQL FROM {$rel['tabela']} ORDER BY LENGTH({$rel['coluna']}) ASC, {$rel['coluna']} ASC LIMIT 50";
        $res = Database::connects()->query($sql);

        $html = "";
        while ($row = $res->fetch_assoc()) {
            $dataAttrs = "";
            $labelExtras = [];
            foreach ($deps as $d) {
                $valExtra = htmlspecialchars($row[$d]);
                $dataAttrs .= " data-{$d}=\"{$valExtra}\"";
                if (!empty($valExtra)) $labelExtras[] = $valExtra;
            }
            $textoExtra = !empty($labelExtras) ? " (" . implode(" - ", $labelExtras) . ")" : "";
            $labelFinal = htmlspecialchars($row[$rel['coluna']]) . $textoExtra;
            $html .= "<div class='custom-option' data-value='{$row[$rel['value']]}' $dataAttrs>$labelFinal</div>";
        }

        $html .= "<div class='select-sentinel' data-tabela='{$rel['tabela']}' data-coluna='{$rel['coluna']}' data-value-col='{$rel['value']}' data-offset='50' data-slug='$slug' data-nome-campo-origem='$name'>Carregando mais...</div>";
        return $html;
    }
}
