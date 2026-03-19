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
    public static function buscaDonoAgendamento($id_agendamento): ?array
    {
        $db = Database::connects();
        $sql = "SELECT usuario.email, usuario.nome 
            FROM usuario
            INNER JOIN agendamentos a ON agendamentos.id_usuario = usuario.id 
            WHERE agendamentos.id = ?";

        $stmt = $db->prepare($sql);
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
