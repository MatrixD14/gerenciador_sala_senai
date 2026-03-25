<?php
class Delete
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require_once __DIR__ . '/../../arrayTables.php';
    }

    public static function delete()
    {
        self::loadConfig();
        $connect = Database::connects();
        $table = $_POST["table"];
        $id = $_POST["id"];
        $name = $_POST["name"];
        if (!isset(self::$inforDate[$table])) {
            Tabelas::log_error_table("Tabela não encontrada");
            header("Location: /$table");
            exit;
        }
        $config = self::$inforDate[$table];
        $nomeTabela = $config["tabela"];
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
            $checkSql = "SELECT $colunaData FROM $nomeTabela WHERE id = ?";
            $stmtCheck = $connect->prepare($checkSql);
            $stmtCheck->bind_param("i", $id);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();

            if ($row = $resCheck->fetch_assoc()) {
                $dataRegistro = $row[$colunaData];
                if (!empty($dataRegistro) && $dataRegistro < $hoje) {
                    Tabelas::log_error_table("Não e possivel deletar registros antigos!!");
                    header("Location: /$table");
                    exit;
                }
            }
            $stmtCheck->close();
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
        $stmt = $connect->prepare("delete from $nomeTabela  where id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) Tabelas::log_error_table("deleto como sucesso " . htmlspecialchars($name));
        else Tabelas::log_error_table("Nenhum registro encontrado para deletar.");
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
