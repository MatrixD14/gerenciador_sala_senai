<?php
class editor
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require_once __DIR__ . '/../../arrayTables.php';
    }
    public static function geraeditor($table, $id): string
    {
        self::loadConfig();
        $config =  self::$inforDate[$table] ?? null;
        if ($config === null) return "Configuração não encontrada";
        $tabela = $config['tabela'] ?? null;
        $coluneSelect = $config['colunas'] ?? null;
        if ($coluneSelect === null) return "Tabela não encontrada";
        $result = Tabelas::list_All("select * from $tabela where id = $id");
        $dadosAtuais = $result->fetch_assoc() ?? [];
        $html = "";
        foreach ($coluneSelect as $coluna => $tipo) {
            if ($coluna === "id") continue;
            $valorNoBanco = $dadosAtuais[$coluna] ?? '';
            $labelBonito = str_replace(['idUser', 'idSala'], ['Usuário', 'Sala'], $coluna);
            if ($labelBonito == $coluna) {
                $labelBonito = ucfirst(str_replace(['id', '_'], ['', ' '], $coluna));
            }
            $html .= "<label for='$coluna'>$labelBonito</label><br>";
            if (is_array($tipo) && !isset($tipo["tabela"])) {
                $html .= "<select name='$coluna' id='$coluna'>";
                foreach ($tipo as $valor) {
                    $selected = ($valor == $valorNoBanco) ? "selected" : "";
                    $html .= "<option value='$valor' $selected>$valor</option>";
                }
                $html .= "</select>";
            } elseif (is_array($tipo) && isset($tipo['tabela'])) {
                $origem = $tipo['tabela'];
                $label = $tipo['coluna'];
                $registrosExt = Tabelas::list_All("select id, $label from $origem");

                $html .= "<select name='$coluna' id='$coluna'>";
                while ($reg = $registrosExt->fetch_assoc()) {
                    $selected = ($reg['id'] == $valorNoBanco) ? "selected" : "";
                    $html .= "<option value='{$reg['id']}' $selected>{$reg[$label]}</option>";
                }
                $html .= "</select>";
            } else {
                $valorEscapado = htmlspecialchars($valorNoBanco);
                $html .= "<input type='$tipo' name='$coluna' id='$coluna' value='$valorEscapado'>";
            }
            $html .= "<br><br>";
        }
        return $html;
    }
    public static function editoDados()
    {
        self::loadConfig();
        $table = $_POST['table'] ?? null;
        $id = $_POST['id'] ?? null;
        if (!$table || !$id) return "Dados insuficientes para editar.";
        $config =  self::$inforDate[$table] ?? null;
        if (!$config) return "Configuração da tabela não encontrada.";
        $tabela = $config['tabela'] ?? null;
        $coluneSelect = $config['colunas'] ?? null;
        if ($coluneSelect === null) return "Tabela não encontrada";

        $camposParaAtualizar = [];
        $valores = [];

        foreach ($coluneSelect as $coluna => $tipo) {
            if ($coluna === "id") continue;
            if (is_array($tipo) && isset($tipo['visual']) && $tipo['visual'] === true) {
                continue;
            }
            $nomeColunaBanco = $coluna;
            if (is_array($tipo) && isset($tipo['campo_real']))
                $nomeColunaBanco = $tipo['campo_real'];
            if (isset($_POST[$coluna])) {
                $valor = $_POST[$coluna];
                $camposParaAtualizar[] = "$nomeColunaBanco = ?";
                $valores[] = $valor;
            }
        }
        if (empty($camposParaAtualizar)) return "Nenhuma alteração detectada.";
        $sql = "UPDATE $tabela SET " . implode(", ", $camposParaAtualizar) . " WHERE id = ?";
        $valores[] = $id;
        $db = Database::connects();

        $stmt = $db->prepare($sql);
        $tipos = str_repeat("s", count($valores) - 1) . "i";

        $stmt->bind_param($tipos, ...$valores);
        if ($stmt->execute()) {
            Tabelas::log_error_table("edito como sucesso, $id");
        } else {
            Tabelas::log_error_table("Erro ao atualizar: ") . $stmt->error;
        }
        header("Location: /$table");
        exit;
    }
}
