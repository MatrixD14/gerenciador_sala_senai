<?php
class RecuperarPassWord
{
    
    public static function criaToken($idUsuario)
    {
        $token = bin2hex(random_bytes(32));
        $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $db = Database::connects();
        $stmt = $db->prepare("insert into recuperacao_tokens (idUser, token, expiracao) values (?, ?, ?)");
        $stmt->bind_param("iss", $idUsuario, $token, $expiracao);
        $stmt->execute();
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
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['reset_user_id'] = $resultado['idUser'];
            $_SESSION['reset_token'] = $tokenURL;
            header('location: /mudar_password');
        } else {
            echo "Link inválido ou expirado.";
        }
        exit;
    }
    public static function mudarpassword()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = $_SESSION['reset_user_id'] ?? null;
        $token = $_SESSION['reset_token'] ?? null;
        $pass = $_POST['senha'] ?? null;

        if (!$userId || !$pass || !$token) {
            die("Sessão expirada ou dados inválidos.");
        }

        $db = Database::connects();
        $passwordHash = password_hash($pass, PASSWORD_DEFAULT);

        $sqlPass = "UPDATE usuario SET senha = ? WHERE id = ?";
        $stmt = $db->prepare($sqlPass);
        $stmt->bind_param("si", $passwordHash, $userId);
        $stmt->execute();
        $sqlToken = "UPDATE recuperacao_tokens SET usado = 1 WHERE token = ?";
        $stmtToken = $db->prepare($sqlToken);
        $stmtToken->bind_param("s", $token);
        $stmtToken->execute();

        unset($_SESSION['reset_user_id'], $_SESSION['reset_token']);

        header('Location: /login?sucesso=1');
        exit;
    }
}
