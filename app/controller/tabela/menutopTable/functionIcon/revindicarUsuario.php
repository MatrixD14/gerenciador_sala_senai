<?php
class revindicar
{
    public static function EnviaRevidicacao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id_agendamento = $_POST["id"] ?? null;
        $nome = $_POST["usuario"] ?? null;
        $id_remetente = $_SESSION["id"] ?? null;
        $mensagem = !empty($_POST["menssage"]) ? $_POST["menssage"] : "Gostaria de usar essa sala.";

        if ($id_agendamento && $id_remetente) {
            revindicando::EnviaRevidicacao(
                $id_remetente,
                $id_agendamento,
                $mensagem
            );
            Tabelas::log_error_table("Você revindico um agendamento com ID $nome,  sucesso!");
        } else {
            Tabelas::log_error_table("Erro: Dados insuficientes para reivindicar.");
        }
    }
}
