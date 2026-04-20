<?php
class Delete
{
    private  static $inforDate;

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

    public static function delete()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userLogadoId = $_SESSION['id'] ?? null;
        $userPrivilegio = $_SESSION['privilegio'] ?? 'aluno';
        self::loadConfig();
        $connect = Database::connects();
        $table = $_POST["table"];
        $id = $_POST["id"];
        $name = $_POST["nome"];
        if (!isset(self::$inforDate[$table])) {
            Tabelas::log_error_table("Tabela não encontrada");
            header("Location: /$table");
            exit;
        }
        $config = self::$inforDate[$table];
        $nomeTabela = $config["tabela"];
        if ($userPrivilegio !== 'admin') {
            $colunas = $config["colunas"];
            $colunaData = null;
            foreach ($colunas as $nomeCol => $prop) {
                if (($prop['type'] ?? '') === 'date') {
                    $colunaData = $prop['maskname'] ?? $nomeCol;
                    break;
                }
            }
            if ($colunaData) {
                $hoje = date('Y-m-d');
                $stmtCheck = $connect->prepare("SELECT $colunaData FROM $nomeTabela WHERE id = ?");
                $stmtCheck->bind_param("i", $id);
                $stmtCheck->execute();
                $res = $stmtCheck->get_result()->fetch_assoc();

                if ($res && !empty($res[$colunaData]) && $res[$colunaData] < $hoje) {
                    Tabelas::log_error_table("Usuários normais não podem deletar registros antigos!");
                    header("Location: /$table");
                    exit;
                }
            }
        }

        if (!empty($config["dependencias"])) {
            foreach ($config["dependencias"] as $dep) {
                $tmp = self::countWhere($dep["tabela"], $dep["coluna"], $id);
                if ($tmp > 0) {
                    Tabelas::log_error_table(
                        "Não é possível deletar, existem $tmp 
                <a style='text-decoration:none;' href='/{$dep['link']}'>{$dep['mensagem']}</a> vinculados."
                    );
                    header("Location: /$table");
                    exit;
                }
            }
        }
        if ($userPrivilegio === 'admin') {
            $sql = "DELETE FROM $nomeTabela WHERE id = ?";
            $stmt = $connect->prepare($sql);
            $stmt->bind_param("i", $id);
        } else {
            $colunaDono = $config['owner_column'] ?? 'idUser';
            if (isset($config['owner_relation'])) {
                $rel = $config['owner_relation'];
                $sql = "DELETE t FROM $nomeTabela t 
            INNER JOIN {$rel['tabela']} r ON t.{$rel['coluna']} = r.{$rel['value']}
            WHERE t.id = ? AND r.{$rel['owner_column']} = ?";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("ii", $id, $userLogadoId);
            } else {
                $sql = "DELETE FROM $nomeTabela WHERE id = ? AND $colunaDono = ?";
                $stmt = $connect->prepare($sql);
                $stmt->bind_param("ii", $id, $userLogadoId);
            }
        }
        try {
            $stmt->execute();
            if ($stmt->affected_rows > 0) Tabelas::log_error_table("deleto como sucesso " . htmlspecialchars($name));
            else Tabelas::log_error_table("Nenhum registro encontrado para deletar.");
        } catch (mysqli_sql_exception $e) {
            Tabelas::log_error_table("Erro: Não foi possível deletar pois este registro está sendo usado em outra tabela.");
        }
        $stmt->close();
        header("Location: /$table");
        exit;
    }
    public static function countWhere($table, $column, $value)
    {
        $connect = Database::connects();
        $sql = "select COUNT(*) as total from $table where $column=?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $value);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row["total"];
    }
}
