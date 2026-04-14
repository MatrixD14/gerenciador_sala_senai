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
            $id_nova_reivindicacao = revindicando::EnviaRevindicacao(
                $id_remetente,
                $id_agendamento,
                $mensagem
            );
            $dadosEmail = BuscaInfoUser::buscaDonoAgendamento($id_agendamento);
            $nomeQuemPede = $_SESSION["nome"] ?? "Alguém";

            // if ($dadosEmail && isset($dadosEmail['email'])) {
            //     EnviaInfoEmail::dispararEmailNotificacao($nomeQuemPede, $dadosEmail['email'], $dadosEmail['sala'], $mensagem, $id_nova_reivindicacao);
            // }
            Tabelas::log_error_table("Você revindico um agendamento com ID $nome,  sucesso!");
        } else {
            Tabelas::log_error_table("Erro: Dados insuficientes para reivindicar.");
        }
    }
    public static function ConfirmoRevidicacao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $id_agendamento = $_REQUEST["id"] ?? null;
        $status = $_REQUEST["status"] ?? null;

        if ($id_agendamento && $status) {
            $repostaServe =  revindicando::confirmoRevindicacao($id_agendamento, $status);
            $repost = $repostaServe === "sucesso" ? "Você $status com," : "";
            Tabelas::log_error_table("$repost $repostaServe");
        } elseif ($id_agendamento || $status) {
            Tabelas::log_error_table("Erro: Dados insuficientes para reivindicar. ID: $id_agendamento");
        }
    }
    public static function ExperarReivindicacao()
    {
        date_default_timezone_set('America/Sao_Paulo');
        $db = Database::connects();
        $hoje = date('Y-m-d');
        $stmtAntigos = $db->prepare("UPDATE requisicoes_troca SET status = 'expiro' WHERE DATE(data_envio) < ? AND status = 'pendente'");
        $stmtAntigos->bind_param("s", $hoje);
        $stmtAntigos->execute();
        $stmtAntigos->close();
    }
}
