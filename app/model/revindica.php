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
            INSERT INTO revindicados 
            (id_remetente, id_agendamento_revindicado, mensagem) 
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("iis", $id_remetente, $id_destinatario, $mensagem);
        if (!$stmt->execute()) {
            throw new Exception("Erro no Execute: " . $stmt->error);
        }

        return true;
    }
    public static function confirmoRevindicacao(
        $id,
        $status
    ) {
        $db = Database::connects();
        if (!$db) throw new Exception("Falha na conexão com o banco.");
        $stmt = $db->prepare("
            update revindicados set
            status=? where id=?
            ");

        $stmt->bind_param("si", $status, $id);
        if (!$stmt->execute()) {
            throw new Exception("Erro no Execute: " . $stmt->error);
        }

        return true;
    }
}
