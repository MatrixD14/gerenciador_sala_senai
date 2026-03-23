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
    public static function EnviaRevindicacao($id_remetente, $id_destinatario, $mensagem)
    {
        $db = Database::connects();
        $stmt = $db->prepare("INSERT INTO revindicados (...) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_remetente, $id_destinatario, $mensagem);
        $stmt->execute();

        return $db->insert_id;
    }
    public static function buscaIdName($id): ?array
    {
        $db = Database::connects();
        $stmtUser = $db->prepare("SELECT name FROM usuario WHERE id = ?");
        $stmtUser->bind_param("i", $id);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result()->fetch_assoc();
        return $resUser;
    }
    public static function buscaDonoAgendamento($id_agendamento): ?array
    {
        $db = Database::connects();
        $sql = "SELECT s.email, s.name as usuario, sl.name as sala
            FROM usuario s
            INNER JOIN agendar_sala a ON a.idUser = s.id 
            INNER JOIN sala sl ON a.idSala = sl.id 
            WHERE a.id = ?";

        $stmt = $db->prepare($sql);
        if (!$stmt) throw new Exception("Erro no SQL: " . $db->error);
        $stmt->bind_param("i", $id_agendamento);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public static function buscaBancoInfo($tabela, $colValue, $colLabel, $offset, $limit, $extraCols = [], $search = '')
    {
        $db = Database::connects();
        $colsSql = implode(', ', array_merge([$colValue, $colLabel], $extraCols));

        $where = "";
        $params = [];
        $types = "";
        if (!empty($search)) {
            $terms = explode(' ', $search);
            $allConditions = [];

            foreach ($terms as $term) {
                $term = trim($term);
                if (empty($term)) continue;

                $searchParam = "%$term%";
                $colConditions = [];
                $colConditions[] = "$colLabel LIKE ?";
                $params[] = $searchParam;
                $types .= "s";
                foreach ($extraCols as $colExtra) {
                    $colConditions[] = "$colExtra LIKE ?";
                    $params[] = $searchParam;
                    $types .= "s";
                }
                $allConditions[] = "(" . implode(" OR ", $colConditions) . ")";
            }
            if (!empty($allConditions)) {
                $where = " WHERE " . implode(" AND ", $allConditions);
            }
        }
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        $types .= "ii";

        $sql = "SELECT $colsSql FROM $tabela $where 
            ORDER BY LENGTH($colLabel) ASC, $colLabel ASC 
            LIMIT ? OFFSET ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro na preparação da query: " . $db->error);
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result();
    }
}
