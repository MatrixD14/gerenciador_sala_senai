<?php
class Inserted
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require_once __DIR__ . '/../../arrayTables.php';
    }
    public static function inserted()
    {
        self::loadConfig();
        $table = $_POST['table'] ?? null;
        if (!$table) return "Dados insuficientes para editar.";
        $config =  self::$inforDate[$table] ?? null;
        if (!$config) return "Configuração da tabela não encontrada.";
        $tabela = $config['tabela'] ?? null;
        $coluneSelect = $config['colunas'] ?? null;
        if ($coluneSelect === null) return "Configuração de colunas não encontrada.";
        $colunasBanco = [];
        $placeholders = [];
        $valores = [];
        $tipos = "";

        foreach ($coluneSelect as $nomeNoForm => $conf) {
            if (!empty($conf['primary'])) continue;
            $nomeColunaBanco = $conf['maskname'] ?? $nomeNoForm;
            if (isset($_POST[$nomeNoForm])) {
                $valor = $_POST[$nomeNoForm];

                $colunasBanco[] = $nomeColunaBanco;
                $placeholders[] = "?";
                $valores[] = $valor;
                $tipos .= ($conf['type'] ?? '') === 'number' ? "i" : "s";
            }
        }

        if (empty($colunasBanco)) return "Nenhum dado enviado para inserção.";

        $db = Database::connects();
        $sql = "insert into $tabela (" . implode(", ", $colunasBanco) . ") values (" . implode(", ", $placeholders) . ")";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            Tabelas::log_error_table("Erro no Prepare: " . $db->error);
            header("Location: /$table");
            exit;
        }
        $stmt->bind_param($tipos, ...$valores);

        if ($stmt->execute()) {
            $novoId = $db->insert_id;
            Tabelas::log_error_table("Inserido com sucesso na tabela $tabela, Novo ID: $novoId");
        } else {
            Tabelas::log_error_table("Erro ao inserir na tabela: " . $stmt->error);
        }
        header("Location: /$table");
        exit;
    }
}
