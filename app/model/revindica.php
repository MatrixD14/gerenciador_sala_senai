<?php
class revindicando
{
    public static function EnviaRevidicacao(
        $id_remetente,
        $id_destinatario,
        $mensagem
    ) {
        $db = Database::connects();
        if (!$db) throw new Exception("Falha na conexão com o banco.");
        $stmt = $db->prepare("
            INSERT INTO revindicados 
            (id_remetente, id_agendamento_revindicado, mensagem) 
            VALUES (?, ?, ?)
        ");
        if (!$stmt) throw new Exception("Erro no Prepare: " . $db->error);

        $stmt->bind_param("iis", $id_remetente, $id_destinatario, $mensagem);
        if (!$stmt->execute()) {
            throw new Exception("Erro no Execute: " . $stmt->error);
        }

        return true;
    }
}
