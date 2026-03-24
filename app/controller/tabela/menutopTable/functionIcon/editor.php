<?php
class editor
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require_once __DIR__ . '/../../arrayTables.php';
    }
    public static function editoDados()
    {
        self::loadConfig();
        $table = $_POST['table'] ?? null;
        $id = $_POST['id'] ?? null;
        if (!$table || !$id) {
            header('location: /gerenciado_de_Sala');
            exit;
        }
        $config =  self::$inforDate[$table] ?? null;
        if (!$config) return "Configuração da tabela não encontrada.";
        $tabela = $config['tabela'] ?? null;
        $coluneSelect = $config['colunas'] ?? null;
        if ($coluneSelect === null) return "Configuração de colunas não encontrada.";

        $camposParaAtualizar = [];
        $valores = [];
        $tipos = "";

        foreach ($coluneSelect as $nomeNoForm => $conf) {
            if (!empty($conf['primary'])) continue;
            if (!empty($conf['virtual'])) continue;
            $nomeColunaBanco = $conf['maskname'] ?? $nomeNoForm;
            if (isset($_POST[$nomeNoForm])) {
                $valor = $_POST[$nomeNoForm];

                $camposParaAtualizar[] = "$nomeColunaBanco = ?";
                $valores[] = $valor;
                $tipos .= (($conf['type'] ?? '') === 'number' || (!empty($conf['relation']))) ? "i" : "s";
            }
        }

        if (empty($camposParaAtualizar)) return "Nenhuma alteração detectada.";

        $db = Database::connects();
        $sql = "update $tabela set " . implode(", ", $camposParaAtualizar) . " where id = ?";
        $valores[] = $id;
        $tipos .= "i";
        $stmt = $db->prepare($sql);

        $stmt->bind_param($tipos, ...$valores);
        if ($stmt->execute()) {
            Tabelas::log_error_table("Editado com sucesso, ID: $id na tabela $table");
        } else {
            Tabelas::log_error_table("Erro ao atualizar: " . $stmt->error);
        }
        header("Location: /$table");
        exit;
    }
}
