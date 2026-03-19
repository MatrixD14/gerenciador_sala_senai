<?php

class revindicar
{
    public static function EnviaRevindicacao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id_agendamento = $_POST["id"] ?? null;
        $nome = $_POST["usuario"] ?? null;
        $id_remetente = $_SESSION["id"] ?? null;
        $mensagem = !empty($_POST["menssage"]) ? $_POST["menssage"] : "Gostaria de usar essa sala.";

        if ($id_agendamento && $id_remetente) {
            revindicando::EnviaRevindicacao(
                $id_remetente,
                $id_agendamento,
                $mensagem
            );
            $dadosEmail = BuscaInfoUser::buscaDonoAgendamento($id_agendamento);

            if ($dadosEmail && isset($dadosEmail['email'])) {
                // EnviaInfoEmail::dispararEmailNotificacao($dadosEmail['email'], $mensagem);
            }
            Tabelas::log_error_table("Você revindico um agendamento com ID $nome,  sucesso!");
        } else {
            Tabelas::log_error_table("Erro: Dados insuficientes para reivindicar.");
        }
    }
    public static function ConfirmoRevidicacao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id_agendamento = $_POST["id"] ?? null;
        $status = $_POST["status"] ?? null;

        if ($id_agendamento && $status) {
            revindicando::confirmoRevindicacao($id_agendamento, $status);
            Tabelas::log_error_table("Você reivindico um agendamento com,  sucesso!");
        } else {
            Tabelas::log_error_table("Erro: Dados insuficientes para reivindicar.");
        }
    }
    public static function ExperarReivindicacao()
    {
        $db = Database::connects();
        $hoje = date('Y-m-d');
        $horaLimite = 22;
        $agoraHora = (int)date('H');
        $stmt = $db->prepare("update revindicados set status = 'expiro' where date(data_envio) < ? and status = 'pendente'");
        if ($agoraHora >= $horaLimite) $stmt = $db->prepare("update revindicados set status = 'expiro' where date(data_envio) < ? and status = 'pendente'");
        $stmt->bind_param("s", $hoje);
        $stmt->execute();
        $stmt->close();
    }
}
