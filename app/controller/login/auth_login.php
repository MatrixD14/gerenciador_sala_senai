<?php

class AuthLogin
{
    public static function log_error($log)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION["log_create"] = $log;
        header('location: /');
        exit;
    }
    public static function login()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user = $_POST['nome'] ?? '';
        $pass = $_POST['senha'] ?? '';
        if (isset($user, $pass)) {
            if (strlen($user) == 0) self::log_error("preencha o campo nome");
            else if (strlen($pass) == 0) self::log_error("preenchao campo senha");
            else {
                if (!User::checkPassword($user, $pass))
                    self::log_error("não existe nenhum <br>registro seu crie um");

                $_SESSION['nome'] = $user;
                if (User::checkPrivilegio($user) === "admin")
                    header('Location: /admin');
                else header('Location: /gerenciador_sala');
                $_SESSION["privilegio"] = User::checkPrivilegio($user);
                exit;

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
        $user = $_POST['nome'] ?? '';
        $pass = $_POST['senha'] ?? '';
        $email = $_POST['email'] ?? '';
        if (isset($user, $pass, $email)) {
            if (strlen($user) == 0) self::log_error("preencha o campo nome");
            else if (strlen($pass) == 0) self::log_error("preenchao campo senha");
            else if (strlen($email) == 0) self::log_error("preenchao campo email");
            else {
                if (User::SelectUsercheck("usuario", $user)->num_rows > 0 || User::SelectUsercheck("email", $email)->num_rows > 0) {
                    $_SESSION["log_create"] = "usuario ja existe";
                    header('Location: /cadastrar');
                    exit;
                } elseif (User::checkCadastro($user, $pass, $email)) {
                    self::log_error("conta criada com sucesso");
                } else self::log_error("erro ao create conta");
                Database::close();
            }
        }
    }
}
