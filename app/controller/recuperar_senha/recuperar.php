<?php
class recuperar
{
    public static function log_error_token($log)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION["erro_token"] = $log;
    }
    public static function geraToken()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        RecuperarPassWord::limparTokensExpirados();
        error_log("POST em geraToken: " . print_r($_POST, true));
        $email = isset($_POST['email']) ? trim($_POST['email']) : null;
        error_log("Email após trim: '$email'");

        if (!$email) {
            self::log_error_token("Nenhum e-mail foi enviado.");
            header("location: /EmailRecuperacao");
            exit;
        }
        $_SESSION['reset_email'] = $email;
        $_SESSION['last_request_time'] = time();
        $usuario = BuscaInfoUser::buscaEmail($email);
        if (!$usuario) {
            self::log_error_token("Email não encontrado");
            header("location: /EmailRecuperacao");
            exit;
        }
        $token = RecuperarPassWord::criaToken($usuario['id']);
        $dadosUser = BuscaInfoUser::buscaIdName($usuario['id']);
        $nome = $dadosUser['nome'] ?? 'Usuário';
        $enviado = EnviaInfoEmail::dispararEmailRecuperacao($email, $nome, $token);

        if ($enviado) {
            header('location: /verificarToken');
        } else {
            self::log_error_token("Erro ao enviar e-mail. Tente novamente mais tarde.");
            header('Location: /EmailRecuperacao');
        }
        exit;
    }
    public static function regerarToken()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $email = $_SESSION['reset_email'] ?? null;
        if (!$email) {
            header('Location: /EmailRecuperacao');
            exit;
        }

        $ultimoPedido = $_SESSION['last_request_time'] ?? 0;
        $agora = time();
        if ($agora - $ultimoPedido < 300) {
            $restante = 300 - ($agora - $ultimoPedido);
            $_SESSION['erro_token'] = "Aguarde " . ceil($restante / 60) . " minutos para solicitar um novo token.";
            header('Location: /geraToken');
            exit;
        }

        $usuario = BuscaInfoUser::buscaEmail($email);
        if (!$usuario) {
            unset($_SESSION['reset_email']);
            header('Location: /EmailRecuperacao');
            exit;
        }

        $db = Database::connects();
        $stmtDel = $db->prepare("UPDATE recuperacao_token SET usado = 1 WHERE idUser = ? AND usado = 0");
        $stmtDel->bind_param("i", $usuario['id']);
        $stmtDel->execute();

        $token = RecuperarPassWord::criaToken($usuario['id']);
        $dadosUser = BuscaInfoUser::buscaIdName($usuario['id']);
        $nome = $dadosUser['nome'] ?? 'Usuário';

        $enviado = EnviaInfoEmail::dispararEmailRecuperacao($email, $nome, $token);

        if ($enviado) {
            $_SESSION['last_request_time'] = time(); // atualiza o timestamp
            $_SESSION['sucesso_token'] = "Um novo token foi enviado para seu e-mail.";
        } else {
            self::log_error_token("Erro ao enviar e-mail. Tente novamente mais tarde.");
            header('Location: /EmailRecuperacao');
            exit;
        }

        header('Location: /geraToken');
        exit;
    }
}
