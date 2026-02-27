<?php
class Database
{
    public static $connect;
    public static function connects()
    {
        if (self::$connect === null) {
            $database = Env::get('dataBase');
            if (!$database) {
                die("Seção dataBase não encontrada no .editorConf");
            }
            self::$connect = new mysqli($database["HOST"], $database["USER"], $database["PASSWORD"], $database["DATABASE"], $database["PORT"]);
            if (self::$connect->connect_error) die("error connect on database" . self::$connect->connect_error);
        }
        return self::$connect;
    }
    public static function close()
    {
        if (self::$connect) self::$connect->close();
    }
}
