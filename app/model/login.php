<?php
class User
{
    public static function SelectUsercheck(string $campo, string $valor)
    {
        $connect = Database::connects();
        $permitidos = ['usuario', 'email'];
        if (!in_array($campo, $permitidos)) {
            die("Campo inválido");
        }
        $sql = "select * from login where $campo=?";
        $tmg = $connect->prepare($sql);
        $tmg->bind_param("s", $valor);
        if (!$tmg->execute()) die("commad nao executado");
        $result = $tmg->get_result() or die("falha na execução do command " . $connect->connect_error);
        $tmg->close();
        return $result;
    }
    public static function checkPrivilegio(string $user): string
    {
        $result = User::SelectUsercheck("usuario", $user);
        if ($result->num_rows != 1) return  "normal";
        $data = $result->fetch_assoc();
        return $data["previlegio"];
    }
    public static function checkPassword(string $user, string $pass): bool
    {
        $result = User::SelectUsercheck("usuario", $user);
        if ($result->num_rows != 1) return false;
        $data = $result->fetch_assoc();
        return password_verify($pass, $data["senha"]);
    }
    public static function checkCadastro(string $user, string $pass, string $email): bool
    {
        $connect = Database::connects();
        $password = password_hash($pass, PASSWORD_DEFAULT);
        $tmg = $connect->prepare("insert into login(usuario,email,senha,previlegio)values(?,?,?,?)");
        $priv = 'normal';
        $tmg->bind_param("ssss", $user, $email, $password, $priv);
        $result = $tmg->execute();
        $tmg->close();
        return $result;
    }
}
