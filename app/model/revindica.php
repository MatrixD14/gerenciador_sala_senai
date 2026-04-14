<?php
class revindicando
{
    public static function EnviaRevindicacao(
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
        $stmtCheck = $db->prepare("SELECT status FROM requisicoes_troca WHERE id = ?");
        $stmtCheck->bind_param("i", $id);
        $stmtCheck->execute();
        $resultado = $stmtCheck->get_result()->fetch_assoc();

        if (!$resultado) return "nao encontrado";

        if ($resultado['status'] === 'aceito' || $resultado['status'] === 'recusado') {
            return "ja processado";
        }
        if ($resultado['status'] === 'expiro') return "já expiro";

        $stmt = $db->prepare("
        UPDATE requisicoes_troca 
        SET status = ? 
        WHERE id = ? 
         AND status = 'pendente'
    ");

        $stmt->bind_param("si", $status, $id);
        if (!$stmt->execute()) {
            throw new Exception("Erro no Execute: " . $stmt->error);
        }
        return ($stmt->affected_rows > 0) ? "sucesso" : "erro";
    }
}
