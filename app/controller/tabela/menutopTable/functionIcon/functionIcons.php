<?php
class FucntIcons
{
    private static $inforDate;

    private static function loadConfig()
    {
        if (!self::$inforDate)
            self::$inforDate = require __DIR__ . '/../../arrayTables.php';
    }
    public static function log_error_table($log)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION["erro_table"] = $log;
    }
    public static function delete()
    {
        self::loadConfig();
        $connect = Database::connects();
        $table = $_POST["table"];
        $id = $_POST["id"];
        $name = $_POST["name"];
        if (!isset(self::$inforDate[$table]))
            self::log_error_table("Tabela não encontrada");
        $config = self::$inforDate[$table];
        $nomeTabela = $config["tabela"];
        $count = 0;
        $stmt = $connect->prepare("SELECT COUNT(*) FROM agendar_sala WHERE idUser=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            self::log_error_table("Não é possível deletar, existem " . $count . " agendamentos vinculados.");
            header("Location: /agendamentos");
            exit;
        } else {
            $tmp = $connect->prepare("delete from $nomeTabela  where id=?");
            $tmp->bind_param("i", $id);
            $tmp->execute();
            $rows = $tmp->affected_rows;
            $tmp->close();

            if ($rows > 0) self::log_error_table("deleto como sucesso " . htmlspecialchars($name));
            else self::log_error_table("Nenhum registro encontrado para deletar.");
        }
        header("Location: /$table");
        exit;
    }
}
