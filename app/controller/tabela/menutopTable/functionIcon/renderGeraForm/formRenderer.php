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

        // Converte data de DD/MM/AAAA para YYYY-MM-DD se necessário
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
        // if ($isLocked) {
        //     $label = self::resolveLabel($selected, $col);
        //     return "<input type='hidden' name='$name' value='$selected'>
        //         <input type='text' class='input-dados' value='$label' readonly  autocomplete='off' style='cursor:not-allowed;'>";
        // }

        $horaAtual = (int)date('H');
        $displayLabel = "Selecione...";
        $rel = $col['relation'] ?? null;
        $relDataAttrs = "";
        $deps = self::findDependents($name, $allCols);
        if ($rel && !empty($selected)) {
            $relDataAttrs = "data-tabela='{$rel['tabela']}' 
                         data-coluna='{$rel['coluna']}' 
                         data-value-col='{$rel['value']}' 
                         data-slug='$slug' 
                         data-nome-campo-origem='$name'";

            $extraCols = !empty($deps) ? ", " . implode(',', $deps) : "";

            $sqlS = "SELECT {$rel['coluna']} $extraCols FROM {$rel['tabela']} WHERE {$rel['value']} = ?";
            $stmtS = Database::connects()->prepare($sqlS);
            $stmtS->bind_param("s", $selected);
            $stmtS->execute();
            $resS = $stmtS->get_result();

            if ($rowS = $resS->fetch_assoc()) {
                $labelPrincipal = $rowS[$rel['coluna']];
                $labelExtras = [];
                foreach ($deps as $d) {
                    if (!empty($rowS[$d])) $labelExtras[] = $rowS[$d];
                }
                $textoExtra = !empty($labelExtras) ? " (" . implode(" - ", $labelExtras) . ")" : "";
                $displayLabel = htmlspecialchars($labelPrincipal . $textoExtra);
            }
        }
        if ($displayLabel === "Selecione..." && !empty($col['options'])) {
            foreach ($col['options'] as $optLabel => $regra) {
                $optVal = is_array($regra) ? $optLabel : $regra;
                if ($optVal == $selected) {
                    $displayLabel = $optLabel;
                    break;
                }
            }
        }

        if ($isLocked) {
            return "<input type='hidden' name='$name' value='$selected'>
                            <input type='text' class='input-dados' value='$displayLabel' readonly autocomplete='off' style='cursor:not-allowed;'>";
        }

        $html = "<div class='custom-select-container' id='container-$name' $relDataAttrs>";
        $html .= "<input type='hidden' name='$name' id='hidden-$name' value='$selected'>";
        $html .= "<input type='text' 
                class='custom-select-trigger select-dados' 
                id='$name' 
                value='$displayLabel' 
                readonly 
                autocomplete='off'
                style='cursor: pointer;'>";
        $html .= "<div class='custom-select-options'>";
        $html .= "<input class='custom-search input-dados' name='search-$name' ' id='search-$name' placeholder='digite dois carecteres...' autofocus>";
        $html .= "<div class='options-scroll-area'>";

        // 1. Opções Estáticas
        if (!empty($col['options'])) {
            foreach ($col['options'] as $optLabel => $regra) {
                $optVal = is_array($regra) ? $optLabel : $regra;
                $classDisabled = "";
                $labelExibicao = $optLabel;

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
        }

        // 2. Opções do Banco (Relações e Dependentes)
        if ($rel) {
            $extraSQL = "";
            if (!empty($deps)) $extraSQL = ", " . implode(', ', array_filter($deps));

            $limit = 50;

            $sql = "SELECT {$rel['value']}, {$rel['coluna']} $extraSQL 
        FROM {$rel['tabela']} 
        ORDER BY LENGTH({$rel['coluna']}) ASC, {$rel['coluna']} ASC 
        LIMIT $limit OFFSET 0";
            $res = Database::connects()->query($sql);
            while ($row = $res->fetch_assoc()) {
                $val = $row[$rel['value']];
                $dataAttrs = "";
                $labelExtras = [];

                foreach ($deps as $d) {
                    $valExtra = htmlspecialchars($row[$d]);
                    $dataAttrs .= " data-{$d}=\"{$valExtra}\"";
                    if (!empty($valExtra))
                        $labelExtras[] = $valExtra;
                }

                $textoExtra = !empty($labelExtras) ? " (" . implode(" - ", $labelExtras) . ")" : "";
                $labelFinal = htmlspecialchars($row[$rel['coluna']]) . $textoExtra;

                $html .= "<div class='custom-option' data-value='$val' $dataAttrs>$labelFinal</div>";
            }

            $html .= "<div class='select-sentinel' 
            data-tabela='{$rel['tabela']}' 
            data-coluna='{$rel['coluna']}' 
            data-value-col='{$rel['value']}' 
            data-offset='50'
            data-slug='$slug'
            data-nome-campo-origem='$name'>Carregando mais...</div>";
        }

        $html .= "</div></div></div>";

        return $html;
    }
}
