<?php
class recuperar
{
    public static function geraToken()
    {
        $email = $_POST['email'] ?? null;
        $usuario = RecuperarPassWord::buscaEmail($email);
        if (!$usuario) {
            echo "Email não encontrado";
            return;
        }
        RecuperarPassWord::criaToken($usuario['id']);
        header('location: /verificar_Token');
        exit;
    }
}
