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
        $urlAceitar = URLs . "/confirmaSolicitacaoTrocaSala?id=$id_nova_reivindicacao&status=aprovado";
        $urlRecusar = URLs . "/confirmaSolicitacaoTrocaSala?id=$id_nova_reivindicacao&status=recusado";

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
            $mail->Subject = "Nova Solicitação Troca de Sala";
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
            $mail->Password   =  self::EnvInfo('EMAILPASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('seu-email@gmail.com', 'Sistema de Agendamento');
            // $mail->addReplyTo('suporte@seusite.com', 'Suporte Técnico');
            $mail->addAddress($emailDestino);

            $mail->isHTML(true);
            $mail->Subject = 'Nova Solicitação Troca Sala';
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
            $mail->Password   =  self::EnvInfo('EMAILPASSWORD');
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
    public static function  dispararEmailResposta($dados)
    {
        $mail = new PHPMailer(true);

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
            $mail->addAddress($dados['email_remetente']);
            $mail->addCC($dados['email_dono']);

            $mail->isHTML(true);
            $mail->Subject = "Comprovante de Solicitação de Troca: Sala {$dados['sala']}";
            $corStatus = ($dados['decisao'] === 'aprovado') ? '#0a0' : '#f00';
            $statusTexto = strtoupper($dados['decisao']);
            $mail->Body = "
        <div style='font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden;'>
            <div style='background: $corStatus; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0; font-size: 18px;'>NOTIFICAÇÃO DE SISTEMA</h1>
                <p style='margin: 5px 0 0; font-weight: bold;'>Status da Troca: $statusTexto</p>
            </div>
            
            <div style='padding: 20px;'>
                <p style='text-align: center; color: #444;'>Esta é uma confirmação automática sobre o processo de solicitação de troca de sala.</p>
                
                <div style='background: #f8f9fa; border-radius: 5px; padding: 15px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px 0; color: #666; border-bottom: 1px solid #eee;'>Sala:</td>
                            <td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee; text-align: right;'>{$dados['sala']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #666; border-bottom: 1px solid #eee;'>Data:</td>
                            <td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee; text-align: right;'>{$dados['dia']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #666; border-bottom: 1px solid #eee;'>Horário:</td>
                            <td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee; text-align: right;'>{$dados['hora']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #666; border-bottom: 1px solid #eee;'>Solicitante:</td>
                            <td style='padding: 8px 0; font-weight: bold; border-bottom: 1px solid #eee; text-align: right;'>{$dados['nome_remetente']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; color: #666;'>Processado por:</td>
                            <td style='padding: 8px 0; font-weight: bold; text-align: right;'>{$dados['nome_dono']}</td>
                        </tr>
                    </table>
                </div>

                <p style='font-size: 0.9em; color: #555; text-align: center; font-style: italic;'>
                    O registro deste agendamento foi atualizado conforme a decisão acima.
                </p>
            </div>

           <div style='background: #eee; padding: 15px; text-align: center; font-size: 0.8em; color: #777;'>
                Este é um comprovante oficial enviado para ambas as partes envolvidas.<br>
                Gerado em: " . date('d/m/Y H:i') . "
            </div>
        </div>";
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail de recuperação: " . $mail->ErrorInfo);
            return false;
        }
    }
}
