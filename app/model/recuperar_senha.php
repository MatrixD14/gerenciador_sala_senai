<?php
class RecuperarPassWord
{
    public static function geraToken()
    {
        $idUsuario = $_POST['id'];
        $token = bin2hex(random_bytes(32));
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $db = Database::connects();
        $stmt = $db->prepare("insert into recuperacao_tokens (idUser, token, expiracao) values (?, ?, ?)");
        $stmt->bind_param("iss", $idUsuario, $token, $expiracao);
        $stmt->execute();
        header('location: /verificar_Token');
    }
    public static function redefinir()
    {
        $tokenURL = $_GET['token'];

        $sql = "SELECT idUser FROM recuperacao_tokens 
        WHERE token = ? AND usado = 0 AND expiracao > NOW()";
        $db = Database::connects();

        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $tokenURL);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();

        if ($resultado) {
            header('location: /mudar_password');
        } else {
            echo "Link inválido ou expirado.";
        }
    }
    public  static function mudarpassword()
    {
        $pass = $_POST['senha'];
        $db = Database::connects();
        $password = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "update table usuario set senha = ? where id=";
        $tmg = $db->prepare($sql);
        $tmg->bind_param("s", $password);
        $tmg->execute();
        $tmg->close();
    }
}
