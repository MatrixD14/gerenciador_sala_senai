<?php
class RelationService
{
    public static function resolve($value, $relConfig)
    {
        if (empty($value)) return "Não informado";
        if (empty($relConfig) && !isset($relConfig['tableConnection'])) {
            return $value;
        }
        $db = Database::connects();
        $currentValue = $value;
        if (isset($relConfig['tableConnection'])) {
            foreach ($relConfig['tableConnection'] as $step) {
                $t = $step['tabela'];
                $b = $step['buscar'];
                $o = $step['onde'];

                $stmt = $db->prepare("SELECT $b FROM $t WHERE $o = ?");
                $stmt->bind_param("s", $currentValue);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();

                if (!$res) return "Não encontrado";
                $currentValue = $res[$b];
            }
            return $currentValue;
        }

        // Relação simples
        if (isset($relConfig['coluna'], $relConfig['tabela'], $relConfig['value'])) {
            $sql = "SELECT {$relConfig['coluna']} FROM {$relConfig['tabela']} WHERE {$relConfig['value']} = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("s", $value);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();

            return $res[$relConfig['coluna']] ?? $value;
        }
        return $value;
    }
    public static function render($name, $config, $currentValue, $isLocked)
    {
        $type = $config['type'] ?? 'text';
        $readonlyAttr = $isLocked ? "readonly style='cursor:not-allowed;'" : "";
        $val = htmlspecialchars($currentValue);

        return match ($type) {
            'hidden' => "<input type='hidden' name='$name' value='$val'>",

            'readonly', 'readonly_user' => "
<input type='hidden' name='$name' value='$val'>
<input type='text' class='input-dados' value='" . self::getLabel($val, $config) . "' readonly style='cursor:not-allowed;'>",

            'date' => "<input type='date' name='$name' class='input-dados' value='$val' $readonlyAttr>",

            'select' => self::renderSelect($name, $config, $val, $isLocked),

            default => "<input type='$type' name='$name' class='input-dados' value='$val' $readonlyAttr>"
        };
    }

    private static function getLabel($val, $config)
    {
        if (isset($config['relation'])) {
            return self::resolve($val, $config['relation']);
        }
        return $val;
    }

    private static function renderSelect($name, $config, $selected, $isLocked)
    {
        if ($isLocked) {
            $label = self::getLabel($selected, $config);
            return "<input type='hidden' name='$name' value='$selected'>
<input type='text' class='input-dados' value='$label' readonly>";
        }

        $html = "<select name='$name' class='select-dados'>";
        // Aqui entraria a lógica de opções fixas ou banco de dados
        $html .= "</select>";
        return $html;
    }
}
