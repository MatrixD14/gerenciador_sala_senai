<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EnviaInfoEmail
{
    private static $password = "";
    public static function dispararEmailNotificacao($nameRemetente, $emailDestino, $SalaDestino, $textoCorpo, $id_nova_reivindicacao)
    {
        $mail = new PHPMailer(true);
        $urlAceitar = URLs . "/confirmaReivindica?id=$id_nova_reivindicacao&status=aceito";
        $urlRecusar = URLs . "/confirmaReivindica?id=$id_nova_reivindicacao&status=recusado";

        try {

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'deivisonjoeldesouzacaldas@gmail.com';
            $mail->Password   =  self::$password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('deivisonjoeldesouzacaldas@gmail.com', 'Sistema de Agendamento');
            // $mail->addReplyTo('suporte@seusite.com', 'Suporte Técnico');
            $mail->addAddress($emailDestino);

            $mail->isHTML(true);
            $mail->Subject = "Nova Reivindicação de Sala";
            $mail->Body = "
                     <h3>Motivo do remetente:</h3>
                     <p>$textoCorpo</p>
                     <br>
                     <p>Aceita que <b>\"$nameRemetente\"</b> utilize sua <b>\"$SalaDestino\"</b> </p>
                     <br>
                     <a href='$urlAceitar' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>ACEITAR</a>
                     &nbsp;&nbsp;
                     <a href='$urlRecusar' style='background-color: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>RECUSAR</a>
                 ";
            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Erro ao enviar: {$mail->ErrorInfo}";
            return false;
        }
    }
    public static function dispararEmailNotificacaoSuporte($emailDestino, $textoCorpo)
    {
        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'seu-email@gmail.com';
            $mail->Password   =  self::$password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('seu-email@gmail.com', 'Sistema de Agendamento');
            // $mail->addReplyTo('suporte@seusite.com', 'Suporte Técnico');
            $mail->addAddress($emailDestino);

            $mail->isHTML(true);
            $mail->Subject = 'Nova Reivindicacao de Sala';
            $mail->Body    = "<h3>Solicitação Recebida</h3><p>$textoCorpo</p>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Erro ao enviar: {$mail->ErrorInfo}";
            return false;
        }
    }

    public static function dispararEmailRecuperacao($emailDestino, $nomeUsuario, $token)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'deivisonjoeldesouzacaldas@gmail.com';
            $mail->Password   =  self::$password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('deivisonjoeldesouzacaldas@gmail.com', 'Sistema de Agendamento');
            $mail->addAddress($emailDestino);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha';
            $mail->Body = "
                        <h2>Olá, $nomeUsuario!</h2>
                        <p>Seu código de recuperação de senha é:</p>
                        <h1 style='background:#f4f4f4; padding:10px; text-align:center;'>$token</h1>
                        <p>Digite esse código no formulário de verificação.</p>
                        <p>Válido por 1 hora.</p>
             ";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail de recuperação: " . $mail->ErrorInfo);
            return false;
        }
    }
}
