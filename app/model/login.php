<?php
class User
{
    private $connect;
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
        $accounts = Env::get('database');

        if (!is_array($accounts))
            return false;

        $user = trim($user);
        $pass = trim($pass);
        $email = trim($email);

        if (!isset($accounts[$user])) return false;
        return trim($accounts[$user]) === $pass;
    }
      public function connects($database){
        $this->connect=new mysqli($database["host"]);
        if($this->connect->connect_error) die("error connect on database".$this->connect->connect_error);
        return $this->connect;
    }
}
