<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EnviaInfoEmail
{
    private static function EnvInfo($key)
    {
        $env = Env::get('SMTP');
        return $env[$key] ?? null;
    }
    public static function dispararEmailNotificacao($nameRemetente, $emailDestino, $SalaDestino, $textoCorpo, $id_nova_reivindicacao)
    {
        $mail = new PHPMailer(true);
        $urlAceitar = URLs . "/confirmaReivindica?id=$id_nova_reivindicacao&status=aprovado";
        $urlRecusar = URLs . "/confirmaReivindica?id=$id_nova_reivindicacao&status=recusado";

        try {

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = self::EnvInfo('EMAIL');
            $mail->Password   =  self::EnvInfo('EMAILPASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(self::EnvInfo('EMAIL'), 'Sistema de Agendamento');
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
            $mail->Password   =  self::EnvInfo('EMAILPASSWORD');;
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
        $link = URLs . "/redefinir?token=$token";

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = self::EnvInfo('EMAIL');
            $mail->Password   =  self::EnvInfo('EMAILPASSWORD');;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(self::EnvInfo('EMAIL'), 'Sistema de Agendamento');
            $mail->addAddress($emailDestino);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperação de Senha';
            $mail->Body = "
                        <h2>Olá, $nomeUsuario!</h2>
                        <p>Você solicitou a recuperação de senha. Clique no botão abaixo para prosseguir:</p>
                        <div style='text-align:center; margin: 30px 0;'>
                            <a href='$link' style='background:#007bff; color:white; padding:15px 25px; text-decoration:none; border-radius:5px;'>REDEFINIR SENHA</a>
                        </div>
                        <p>Se o botão não funcionar, copie e cole o link abaixo no seu navegador:</p>
                        <p>$link</p>
                        <p>Este link expira em 1 hora.</p>
                    ";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail de recuperação: " . $mail->ErrorInfo);
            return false;
        }
    }
}
