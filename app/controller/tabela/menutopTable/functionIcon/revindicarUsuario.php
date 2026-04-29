<?php
class revindicar
{
    public static function EnviaRevindicacao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        Csrf::verify('/gerenciado_de_Sala');
        $id_agendamento = $_POST["id"] ?? null;
        $nome = $_POST["usuario"] ?? null;
        $id_remetente = $_SESSION["id"] ?? null;
        $mensagem = !empty($_POST["menssage"]) ? $_POST["menssage"] : "Gostaria de usar essa sala.";

        if ($id_agendamento && $id_remetente) {
            try {
                $id_nova_reivindicacao = revindicando::enviaRevindicacao(
                    $id_remetente,
                    $id_agendamento,
                    $mensagem
                );
                $dadosEmail = BuscaInfoUser::buscaDonoAgendamento($id_agendamento);
                $nomeQuemPede = $_SESSION["nome"] ?? "Alguém";

                if ($dadosEmail && isset($dadosEmail['email'])) {
                    EnviaInfoEmail::dispararEmailNotificacao($nomeQuemPede, $dadosEmail['email'], $dadosEmail['sala'], $mensagem, $id_nova_reivindicacao);
                }
                Tabelas::log_error_table("Você requisito um agendamento com ID $nome,  sucesso!");
            } catch (Exception $e) {
                Tabelas::log_error_table("Erro ao processar: " . $e->getMessage());
            }
        } else {
            Tabelas::log_error_table("Erro: Dados insuficientes para requisitar a troca.");
        }
    }
    public static function ConfirmoRevidicacao()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $id_solicitacao = $_REQUEST["id"] ?? null;
        $status = $_REQUEST["status"] ?? null;
        $id_usuario_logado = $_SESSION["id"] ?? null;

        if ($id_solicitacao && $status && $id_usuario_logado) {
            try {
                $id_dono_real = BuscaInfoUser::buscaDonoPorTabela('menssagem', $id_solicitacao);

                if ($id_dono_real !== (int)$id_usuario_logado && $_SESSION['privilegio'] !== 'admin') {
                    Tabelas::log_error_table("Erro: Você não tem permissão para processar esta solicitação.");
                    return;
                }

                $repostaServe =  revindicando::confirmoRevindicacao($id_solicitacao, $status);

                if (is_array($repostaServe) && $repostaServe['res'] === "sucesso") {
                    EnviaInfoEmail::dispararEmailResposta($repostaServe);

                    Tabelas::log_error_table("Solicitação de troca finalizada e comprovante enviado: $status!");
                } else {
                    $mensagens = [
                        "sucesso" => "Solicitação processada com sucesso: $status!",
                        "ja processado" => "Esta solicitação já foi respondida anteriormente.",
                        "já expiro" => "Não foi possível processar: O agendamento já expirou.",
                        "nao encontrado" => "Erro: Solicitação não localizada no sistema.",
                        "erro" => "Houve um erro técnico ao atualizar o status."
                    ];
                    Tabelas::log_error_table($mensagens[$repostaServe] ?? "Erro desconhecido.");
                }
            } catch (Exception $e) {
                Tabelas::log_error_table("Erro crítico: " . $e->getMessage());
            }
        } else {
            Tabelas::log_error_table("Erro: Dados insuficientes para completar a ação.");
        }
    }
    public static function ExperarReivindicacao()
    {
        date_default_timezone_set('America/Sao_Paulo');
        $db = Database::connects();
        $hoje = date('Y-m-d');

        $sqlDataAgendamento = "
            UPDATE requisicoes_troca r
            INNER JOIN agendar_sala a ON r.id_agendamento_revindicado = a.id
            SET r.status = 'expirou'
            WHERE a.dia < ? AND r.status = 'pendente'
        ";

        $stmt1 = $db->prepare($sqlDataAgendamento);
        $stmt1->bind_param("s", $hoje);
        $stmt1->execute();
        $totalExpiradosData = $stmt1->affected_rows;
        $stmt1->close();
        $sqlAntigos = "UPDATE requisicoes_troca SET status = 'expirou' WHERE DATE(data_envio) < ? AND status = 'pendente'";
        $dataLimite = date('Y-m-d', strtotime('-2 days'));

        $stmt2 = $db->prepare($sqlAntigos);
        $stmt2->bind_param("s", $dataLimite);
        $stmt2->execute();
        $totalExpiradosTempo = $stmt2->affected_rows;
        $stmt2->close();

        return ($totalExpiradosData + $totalExpiradosTempo);
    }
}
