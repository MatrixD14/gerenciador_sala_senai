<?php
class Csrf
{
    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verify($redirectUrl = '/'): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['log_create'] = "Algo deu errado. Tente novamente.";
            unset($_SESSION['csrf_token']);
            header("Location: $redirectUrl");
            exit;
        }
        unset($_SESSION['csrf_token']);
    }
}
