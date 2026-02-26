<?php
class User
{
    public static $connect;
    public static function conectioncheck(string $user)
    {
        $tmg = self::$connect->prepare("select * from login where usuario=?");
        $tmg->bind_param("s", $user);
        if (!$tmg->execute()) die("commad nao executado");
        $result = $tmg->get_result() or die("falha na execução do command " . self::$connect->connect_error);
        $tmg->close();
        return $result;
    }
    public static function checkPassword(string $user, string $pass): bool
    {
        $result = User::conectioncheck($user);
        if ($result->num_rows != 1) return false;
        $pass = $result->fetch_assoc();
        return password_verify($pass, $pass["senha"]);
    }
    public static function checkCadastro(string $user, string $pass, string $email): bool
    {
        $password = password_hash($pass, PASSWORD_DEFAULT);
        $tmg = self::$connect->prepare("insert into login(usuario,email,senha,previlegio)values(?,?,?)");
        $priv = 'normal';
        $tmg->bind_param("ssss", $user, $email, $password, $priv);
        $result = $tmg->execute();
        $tmg->close();
        return $result;
    }
    public static function connects()
    {
        $database = Env::get('database');
        self::$connect = new mysqli($database["HOST"], $database["USER"], $database["PASSWORD"], $database["DATABASE"], $database["PORT"]);
        if (self::$connect->connect_error) die("error connect on database" . self::$connect->connect_error);
        return self::$connect;
    }
    public static function close()
    {
        if (self::$connect) self::$connect->close();
    }
}
