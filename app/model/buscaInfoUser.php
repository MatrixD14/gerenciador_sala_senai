<?php
class BuscaInfoUser
{
    public static function buscaEmail($email): ?array
    {
        $db = Database::connects();
        $stmtUser = $db->prepare("SELECT id FROM usuario WHERE email = ?");
        $stmtUser->bind_param("s", $email);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result()->fetch_assoc();
        return $resUser;
    }
    public static function buscaIdEmail($id): ?array
    {
        $db = Database::connects();
        $stmtUser = $db->prepare("SELECT email FROM usuario WHERE id = ?");
        $stmtUser->bind_param("i", $id);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result()->fetch_assoc();
        return $resUser;
    }
}
