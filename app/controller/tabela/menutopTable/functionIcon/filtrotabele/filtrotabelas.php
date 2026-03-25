<?php

class FiltroRenderer
{
    private static $configGeral;

    private static function loadConfig()
    {
        if (!self::$configGeral) {
            // Usa o seu array principal para manter a consistência
            self::$configGeral = require __DIR__ . '/../../../arrayTables.php';
        }
    }

    public static function render($slug): string
    {
        self::loadConfig();
        $config = self::$configGeral[$slug] ?? null;
        if (!$config) return "Tabela não configurada.";

        $html = "<div class='filtro-container'>";

        foreach ($config['colunas'] as $name => $col) {
            // Ignora chaves primárias ou campos ocultos/virtuais no filtro
            if (($col['primary'] ?? false) || ($col['type'] ?? '') === 'hidden' || ($col['virtual'] ?? false)) {
                continue;
            }

            $html .= "<div class='grupo-filtro'>";
            $html .= "<h4>" . ucfirst($name) . "</h4>";

            switch ($col['type']) {
                case 'select':
                    $html .= "<div class='grid-check'>";
                    // Se tiver relação com o Banco (Salas, Usuários)
                    if (isset($col['relation'])) {
                        $html .= self::renderOptionsFromDb($name, $col['relation']);
                    }
                    // Se tiver opções fixas (Períodos)
                    elseif (isset($col['options'])) {
                        $html .= self::renderOptionsStatic($name, $col['options']);
                    }
                    $html .= "</div>";
                    break;

                case 'date':
                    $html .= "<div class='date-range-inputs'>";
                    $html .= "<input type='date' name='{$name}_de' class='input-dados' placeholder='De'>";
                    $html .= "<span> até </span>";
                    $html .= "<input type='date' name='{$name}_ate' class='input-dados' placeholder='Até'>";
                    $html .= "</div>";
                    break;

                default:
                    $html .= "<input type='text' name='$name' class='input-dados' placeholder='Buscar em $name...'>";
                    break;
            }
            $html .= "</div>";
        }

        // Seção de visibilidade de colunas (Baseado no array de exibição)
        $html .= self::renderColumnVisibility($config);

        $html .= "</div>";
        return $html;
    }

    private static function renderOptionsFromDb($name, $rel): string
    {
        $db = Database::connects();
        $sql = "SELECT {$rel['value']}, {$rel['coluna']} FROM {$rel['tabela']} ORDER BY {$rel['coluna']} ASC";
        $res = $db->query($sql);
        $html = "";
        while ($row = $res->fetch_assoc()) {
            $html .= "<label class='check-label'>
                        <input type='checkbox' name='{$name}[]' value='{$row[$rel['value']]}'> 
                        " . htmlspecialchars($row[$rel['coluna']]) . "
                      </label>";
        }
        return $html;
    }

    private static function renderOptionsStatic($name, $options): string
    {
        $html = "";
        foreach ($options as $key => $val) {
            $label = is_array($val) ? $key : $val;
            $value = is_array($val) ? $key : $val;
            $html .= "<label class='check-label'>
                        <input type='checkbox' name='{$name}[]' value='$value'> " . ucfirst($label) . "
                      </label>";
        }
        return $html;
    }

    private static function renderColumnVisibility($config): string
    {
        $html = "<div class='grupo-filtro'><h4>Colunas Visíveis</h4><div class='grid-check'>";
        $colunas = !empty($config['especifico']) ? $config['especifico'] : array_keys($config['colunas']);

        foreach ($colunas as $c) {
            $parts = explode(' as ', $c);
            $nomeExibicao = end($parts);
            if (strpos($nomeExibicao, '.') !== false) $nomeExibicao = explode('.', $nomeExibicao)[1];

            $html .= "<label class='check-label'>
                        <input type='checkbox' name='show_cols[]' value='$nomeExibicao' checked> " . ucfirst($nomeExibicao) . "
                      </label>";
        }
        return $html . "</div></div>";
    }
}
