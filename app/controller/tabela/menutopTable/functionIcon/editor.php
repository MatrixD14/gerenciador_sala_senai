<?php
class editor
{
    private static  $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate) {
            $configPath = __DIR__ . '/../../arrayTables.php';
            if (!file_exists($configPath)) {
                throw new Exception("Arquivo de configuração não encontrado: $configPath");
            }
            self::$inforDate = require $configPath;
            if (!is_array(self::$inforDate)) {
                throw new Exception("arrayTables.php deve retornar um array");
            }
        }
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
        if (!$config) {
            $erro = "Configuração da tabela '$table' não encontrada.";
            return $erro;
        }
        $tabela = $config['tabela'] ?? null;
        $coluneSelect = $config['colunas'] ?? null;
        if ($coluneSelect === null) {
            $erro = "Configuração de colunas não encontrada para tabela '$table'.";
            return $erro;
        }

        $camposParaAtualizar = [];
        $valores = [];
        $tipos = "";

        foreach ($coluneSelect as $nomeNoForm => $conf) {
            if (!empty($conf['primary'])) continue;
            if (!empty($conf['virtual']) || !empty($conf['ghost'])) continue;
            $nomeColunaBanco = $conf['maskname'] ?? $nomeNoForm;
            if (isset($_POST[$nomeNoForm])) {
                $valor = $_POST[$nomeNoForm];


                $camposParaAtualizar[] = "$nomeColunaBanco = ?";
                $valores[] = $valor;
                $tipos .= (($conf['type'] ?? '') === 'number' || (!empty($conf['relation']))) ? "i" : "s";
            } else {
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
        $stmt->close();
        $db->close();
        header("Location: /$table");
        exit;
    }
}
