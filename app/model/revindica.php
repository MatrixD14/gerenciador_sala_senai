<?php
class revindicando
{
    public static function EnviaRevidicacao(
        $id_remetente,
        $id_destinatario,
        $mensagem
    ) {
        $db = Database::connects();
        $stmt = $db->prepare("
            INSERT INTO revindicados 
            (id_remetente, id_agendamento_revindicado, mensagem) 
            VALUES (?, ?, ?)
        ");

        $stmt->bind_param("iis", $id_remetente, $id_destinatario, $mensagem);
        $stmt->execute();

        return true;
    }
}
