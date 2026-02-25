<?php
class User
{
    public static function checkPassword(string $user, string $pass): bool
    {
        $database = Env::get('database');
        $accounts=[];

        if (!is_array($accounts))
            return false;

        $user = trim($user);
        $pass = trim($pass);

        if (!isset($accounts[$user])) return false;
        return trim($accounts[$user]) === $pass;
    }
     public static function criar_login(string $user, string $pass, string $email): bool
    {
        $accounts = Env::get('accounts');

        if (!is_array($accounts))
            return false;

        $user = trim($user);
        $pass = trim($pass);
        $email = trim($email);

        if (!isset($accounts[$user])) return false;
        return trim($accounts[$user]) === $pass;
    }
}
