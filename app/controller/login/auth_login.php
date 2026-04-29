<?php

class AuthLogin
{
    public static function log_error($log, $redirect = '/'): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION["log_create"] = $log;
        header("Location: $redirect");
        exit;
    }
    public static function login()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $_POST['nome'] ?? '';
        $pass = $_POST['senha'] ?? '';
        if (isset($user, $pass)) {
            if (strlen($user) == 0) self::log_error("preencha o campo nome", "/");
            else if (strlen($pass) == 0) self::log_error("preenchao campo senha", "/");
            else {
                if (!User::checkPassword($user, $pass)) {
                    self::log_error("não existe nenhum <br>registro seu, crie um", "/");
                } else {
                    $priv = User::checkPrivilegio($user);
                    $id = User::userID($user);
                    $_SESSION['id'] = $id;
                    $_SESSION['nome'] = $user;
                    $_SESSION["privilegio"] = $priv;
                    header('Location: /gerenciado_de_Sala');
                    exit;
                }
                Database::close();
            }
        }
    }


    public static function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: /');
        exit;
    }

    public static function check()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['nome'])) {
            header('Location: /');
            exit;
        }
    }
    public static function cadastro()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        User::verifyOrFail('/cadastrar');
        $user = $_POST['nome'] ?? '';
        $pass = $_POST['senha'] ?? '';
        $passConfirm = $_POST['confirmaSenha'] ?? '';
        $email = $_POST['email'] ?? '';
        $termos = $_POST['termos'] ?? null;
        if (strlen($user) == 0) self::log_error("preencha o campo nome", "/");
        else if (strlen($pass) == 0 || strlen($passConfirm) == 0) self::log_error("preenchao campo senha", "/");
        else if ($pass !== $passConfirm)
            self::log_error("a senha e a senha de confirmação não são iquais", "/");
        else if (strlen($email) == 0) self::log_error("preenchao campo email", "/");
        else if (!$termos) self::log_error("precisa aceitar os termosl", "/");
        else {
            if (User::SelectUsercheck("nome", $user)->num_rows > 0 || User::SelectUsercheck("email", $email)->num_rows > 0) {
                self::log_error("usuario ja existe", ' /cadastrar');
                exit;
            } elseif (User::checkCadastro($user, $pass, $email)) {
                self::log_error("<p style='color: green'>conta criada com sucesso</p>", "/");
            } else self::log_error("erro ao create conta", "/");
            Database::close();
        }
    }
}
