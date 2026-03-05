<?php
class FucntIcons
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require __DIR__ . '/../../arrayTables.php';
    }

    public static function delete()
    {
        self::loadConfig();
        $connect = Database::connects();
        $table = $_POST["table"];
        $id = $_POST["id"];
        $name = $_POST["name"];
        if (!isset(self::$inforDate[$table]))
            Tabelas::log_error_table("Tabela não encontrada");
        $config = self::$inforDate[$table];
        $nomeTabela = $config["tabela"];
        $count = 0;
        if ($table === "usuarios")
            $count = self::countWhere("agendar_sala", "idUser", $id);
        if ($table === "salas")
            $count = self::countWhere("agendar_sala", "idSala", $id);
        if ($count > 0) {
            Tabelas::log_error_table("Não é possível deletar, existem " . $count . " <a style='text-decoration:none;' href='/agendamentos'>agendamentos</a> vinculados.");
        } else {
            $tmp = $connect->prepare("delete from $nomeTabela  where id=?");
            $tmp->bind_param("i", $id);
            $tmp->execute();
            $rows = $tmp->affected_rows;
            $tmp->close();

            if ($rows > 0) Tabelas::log_error_table("deleto como sucesso " . htmlspecialchars($name));
            else Tabelas::log_error_table("Nenhum registro encontrado para deletar.");
        }
        header("Location: /$table");
        exit;
    }
    public static function countWhere($table, $column, $value)
    {
        $connect = Database::connects();

        $sql = "SELECT COUNT(*) as total FROM $table WHERE $column=?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $value);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $stmt->close();

        return $row["total"];
    }
}
