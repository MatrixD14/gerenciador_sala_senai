<?php
class revindicar
{
    public static function EnviaRevidicacao()
    {
        $IdUser = $_POST['id'];
        $buscaEmail = BuscaInfoUser::buscaIdEmail($IdUser);
    }
}
