<?php
class RecuperarPassWord
{
    public static function criaToken($idUsuario)
    {
        self::limparTokensExpirados();
        $token = bin2hex(random_bytes(5));
        $db = Database::connects();
        $stmt = $db->prepare("INSERT INTO recuperacao_token (idUser, token, expiracao) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
        $stmt->bind_param("is", $idUsuario, $token);
        $stmt->execute();
        return $token;
    }
    public static function log_error_token($log)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION["erro_token"] = $log;
    }
    public static function redefinir()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $tokenURL = $_GET['token'];
        if (!$tokenURL) {
            header('Location: /EmailRecuperacao');
            exit;
        }

        $sql = "SELECT idUser FROM recuperacao_token
        WHERE token = ? AND usado = 0 AND expiracao > NOW()";
        $db = Database::connects();

        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $tokenURL);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();

        if ($resultado) {
            $_SESSION['reset_user_id'] = $resultado['idUser'];
            $_SESSION['reset_token'] = $tokenURL;
            header('location: /mudar_password');
        } else {

            self::log_error_token("Link inválido ou expirado.");
            header('Location: /EmailRecuperacao');
        }
        exit;
    }
    public static function mudarpassword()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        error_log("mudarpassword - Sessão recebida: " . print_r($_SESSION, true));

        $userId = $_SESSION['reset_user_id'] ?? null;
        $token = $_SESSION['reset_token'] ?? null;
        $pass = $_POST['senha'] ?? null;
        $confirm = $_POST['confirmar_senha'] ?? '';

        if (!$userId || !$token) {
            self::log_error_token("expirada. Solicite \numa nova recuperação.");
            header('Location: /EmailRecuperacao');
            exit;
        }
        if (empty($pass) || empty($confirm)) {
            self::log_error_token("Preencha os dois campos.");
            header('Location: /mudar_password');
            exit;
        }
        if ($pass !== $confirm) {
            self::log_error_token("As senhas não coincidem.");
            header('Location: /mudar_password');
            exit;
        }
        if (strlen($pass) < 3) {
            self::log_error_token("A senha deve ter pelo menos 3 caracteres.");
            header('Location: /mudar_password');
            exit;
        }

        $db = Database::connects();

        if (!self::tokenIsValid($token, $userId)) {
            self::log_error_token("Token inválido ou já utilizado.\n Solicite uma nova recuperação.");
            header('Location: /EmailRecuperacao');
            exit;
        }

        $passwordHash = password_hash($pass, PASSWORD_DEFAULT);

        $sqlPass = "UPDATE usuario SET senha = ? WHERE id = ?";
        $stmt = $db->prepare($sqlPass);
        $stmt->bind_param("si", $passwordHash, $userId);
        $stmt->execute();
        $sqlToken = "UPDATE recuperacao_token SET usado = 1 WHERE token = ?";
        $stmtToken = $db->prepare($sqlToken);
        $stmtToken->bind_param("s", $token);
        $stmtToken->execute();

        unset($_SESSION['reset_user_id'], $_SESSION['reset_token'], $_SESSION['reset_email'], $_SESSION['last_request_time']);

        header('Location: /');
        exit;
    }

    public static function verificarTokenPost()
    {
        $tokenDigitado = $_POST['token'] ?? '';

        if (empty($tokenDigitado)) {
            self::log_error_token("Digite o token recebido por e-mail.");
            header('Location: /geraToken');
            exit;
        }

        $db = Database::connects();
        $sql = "SELECT idUser FROM recuperacao_token
            WHERE token = ? AND usado = 0 AND expiracao > NOW()";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $tokenDigitado);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();

        if ($resultado) {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $_SESSION['reset_user_id'] = $resultado['idUser'];
            $_SESSION['reset_token'] = $tokenDigitado;
            header('Location: /mudar_password');
        } else {
            $_SESSION['erro_token'] = "Token inválido ou expirado.";
            header('Location: /geraToken');
        }
        exit;
    }
    private static function tokenIsValid($token, $userId): bool
    {
        $db = Database::connects();
        $sql = "SELECT idUser FROM recuperacao_token WHERE token = ? AND usado = 0 AND expiracao > NOW()";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result && $result['idUser'] == $userId;
    }
    public static function limparTokensExpirados()
    {
        $db = Database::connects();
        $sql = "DELETE FROM recuperacao_token WHERE expiracao < NOW() OR (usado = 1 AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY))";
        $db->query($sql);
    }
}
