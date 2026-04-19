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
        self::debug('=== INÍCIO DO PROCESSAMENTO ===');
        self::debug('POST recebido', $_POST);
        self::loadConfig();
        $table = $_POST['table'] ?? null;
        $id = $_POST['id'] ?? null;
        self::debug('Parâmetros iniciais', ['table' => $table, 'id' => $id]);

        if (!$table || !$id) {
            header('location: /gerenciado_de_Sala');
            exit;
        }
        $config =  self::$inforDate[$table] ?? null;
        if (!$config) {
            $erro = "Configuração da tabela '$table' não encontrada.";
            self::debug($erro);
            return $erro;
        }
        $tabela = $config['tabela'] ?? null;
        $coluneSelect = $config['colunas'] ?? null;
        if ($coluneSelect === null) {
            $erro = "Configuração de colunas não encontrada para tabela '$table'.";
            self::debug($erro);
            return $erro;
        }

        $camposParaAtualizar = [];
        $valores = [];
        $tipos = "";

        foreach ($coluneSelect as $nomeNoForm => $conf) {
            if (!empty($conf['primary']) && !empty($conf['virtual'])) continue;
            $nomeColunaBanco = $conf['maskname'] ?? $nomeNoForm;
            if (isset($_POST[$nomeNoForm])) {
                $valor = $_POST[$nomeNoForm];

                self::debug("Campo '$nomeNoForm' recebido", ['valor' => $valor, 'coluna_banco' => $nomeColunaBanco]);

                $camposParaAtualizar[] = "$nomeColunaBanco = ?";
                $valores[] = $valor;
                $tipos .= (($conf['type'] ?? '') === 'number' || (!empty($conf['relation']))) ? "i" : "s";
            } else {
                self::debug("Campo '$nomeNoForm' NÃO está presente no POST");
            }
        }

        if (empty($camposParaAtualizar)) return "Nenhuma alteração detectada.";

        self::debug('Preparando UPDATE', [
            'sql' => "update $tabela set " . implode(", ", $camposParaAtualizar) . " where id = ?",
            'valores' => $valores,
            'tipos' => $tipos,
            'id' => $id
        ]);

        $db = Database::connects();
        $sql = "update $tabela set " . implode(", ", $camposParaAtualizar) . " where id = ?";
        $valores[] = $id;
        $tipos .= "i";
        $stmt = $db->prepare($sql);

        $stmt->bind_param($tipos, ...$valores);
        if ($stmt->execute()) {
            self::debug("SUCCESS: Editado com sucesso, ID: $id na tabela $table");

            Tabelas::log_error_table("Editado com sucesso, ID: $id na tabela $table");
        } else {
            self::debug("ERRO ao executar UPDATE", ['error' => $stmt->error, 'sql' => $sql]);
            Tabelas::log_error_table("Erro ao atualizar: " . $stmt->error);
        }
        $stmt->close();
        $db->close();
        self::debug('Redirecionando para /' . $table);
        header("Location: /$table");
        exit;
    }
    private static function debug($message, $data = null)
    {
        $logFile = __DIR__ . '/../../debug_editor.log'; // ajuste o caminho conforme necessário
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message";
        if ($data !== null) {
            $logMessage .= "\n" . print_r($data, true);
        }
        $logMessage .= "\n--------------------------------\n";
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
