<?php
class revindicando
{
    public static function enviaRevindicacao(
        $id_remetente,
        $id_destinatario,
        $mensagem
    ) {
        $db = Database::connects();
        if (!$db) throw new Exception("Falha na conexão com o banco.");

        $stmt = $db->prepare("
            INSERT INTO requisicoes_troca 
            (id_remetente, id_agendamento_revindicado, mensagem) 
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("iis", $id_remetente, $id_destinatario, $mensagem);
        if (!$stmt->execute()) {
            throw new Exception("Erro no Execute: " . $stmt->error);
        }

        return $db->insert_id;
    }
    public static function confirmoRevindicacao(
        $id,
        $status
    ) {
        $db = Database::connects();
        if (!$db) throw new Exception("Falha na conexão com o banco.");
        $sql = "SELECT r.status, a.dia, a.hora_inicio, a.hora_fim,
                   u_remetente.email as email_remetente, u_remetente.nome as nome_remetente,
                   u_dono.email as email_dono, 
                   u_dono.nome as nome_dono, 
                   s.nome as nome_sala
            FROM requisicoes_troca r
            INNER JOIN agendar_sala a ON r.id_agendamento_revindicado = a.id
            INNER JOIN usuario u_remetente ON r.id_remetente = u_remetente.id
            INNER JOIN usuario u_dono ON a.idUser = u_dono.id
            INNER JOIN sala s ON a.idSala = s.id
            WHERE r.id = ?";

        $stmtCheck = $db->prepare($sql);
        $stmtCheck->bind_param("i", $id);
        $stmtCheck->execute();
        $resultado = $stmtCheck->get_result()->fetch_assoc();

        if (!$resultado) return "nao encontrado";

        if ($resultado['status'] === 'aprovado' || $resultado['status'] === 'recusado') {
            return "ja processado";
        }
        $hoje = date('Y-m-d');
        if ($resultado['dia'] < $hoje || $resultado['status'] === 'expirou') return "já expiro";

        $stmt = $db->prepare("
        UPDATE requisicoes_troca 
        SET status = ? 
        WHERE id = ? 
         AND status = 'pendente'
    ");
        $stmt->bind_param("si", $status, $id);
        if ($stmt->execute()) {
            return [
                "res" => "sucesso",
                "email_remetente" => $resultado['email_remetente'],
                "nome_remetente" => $resultado['nome_remetente'],
                "email_dono" => $resultado['email_dono'],
                "nome_dono" => $resultado['nome_dono'],
                "sala" => $resultado['nome_sala'],
                "dia" => date('d/m/Y', strtotime($resultado['dia'])),
                "hora" => $resultado['hora_inicio'] . " às " . $resultado['hora_fim'],
                "decisao" => $status
            ];
        }
        return "erro";
    }
}
