<?php
class BuscaInfoUser
{
    public static function buscaEmail($email): ?array
    {
        $db = Database::connects();
        $stmtUser = $db->prepare("SELECT id FROM usuario WHERE LOWER(email) = LOWER(?)");
        if (!$stmtUser) {
            throw new Exception("Erro : " . $db->error);
        }
        $stmtUser->bind_param("s", $email);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result()->fetch_assoc();
        return $resUser;
    }
    public static function buscaIdEmail($id): ?array
    {
        $db = Database::connects();
        $stmtUser = $db->prepare("SELECT email FROM usuario WHERE id = ?");
        if (!$stmtUser) {
            throw new Exception("Erro: " . $db->error);
        }
        $stmtUser->bind_param("i", $id);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result()->fetch_assoc();
        return $resUser;
    }
    public static function EnviaRevindicacao($id_remetente, $id_destinatario, $mensagem)
    {
        $db = Database::connects();
        $stmt = $db->prepare("INSERT INTO requisicoes_troca (...) VALUES (?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Erro: " . $db->error);
        }
        $stmt->bind_param("iis", $id_remetente, $id_destinatario, $mensagem);
        $stmt->execute();

        return $db->insert_id;
    }
    public static function buscaIdName($id): ?array
    {
        $db = Database::connects();
        $stmtUser = $db->prepare("SELECT nome FROM usuario WHERE id = ?");
        if (!$stmtUser) {
            throw new Exception("Erro: " . $db->error);
        }
        $stmtUser->bind_param("i", $id);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result()->fetch_assoc();
        return $resUser;
    }
    public static function buscaDonoAgendamento($id_agendamento): ?array
    {
        $db = Database::connects();
        $sql = "SELECT usuario.id as usuario_id,usuario.email, usuario.nome as usuario, sala.nome as sala
            FROM usuario 
            INNER JOIN agendar_sala ON agendar_sala.idUser = usuario.id 
            INNER JOIN sala  ON agendar_sala.idSala = sala.id 
            WHERE agendar_sala.id = ?";

        $stmt = $db->prepare($sql);
        if (!$stmt) throw new Exception("Erro no SQL: " . $db->error);
        $stmt->bind_param("i", $id_agendamento);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public static function buscaDonoPorTabela($tabela, $idRegistro): ?int
    {
        $db = Database::connects();

        switch ($tabela) {
            case 'usuarios':
                $stmt = $db->prepare("SELECT id FROM usuario WHERE id = ?");
                if (!$stmt) {
                    throw new Exception("Erro: " . $db->error);
                }
                $stmt->bind_param("i", $idRegistro);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                return $row ? (int)$row['id'] : null;

            case 'agendar_sala':
                $stmt = $db->prepare("SELECT idUser FROM agendar_sala WHERE id = ?");
                if (!$stmt) {
                    throw new Exception("Erro: " . $db->error);
                }
                $stmt->bind_param("i", $idRegistro);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                return $row ? (int)$row['idUser'] : null;

            case 'menssagem':
            case 'requisicoes_troca':
                // Aqui buscamos o dono do agendamento que está sendo reivindicado
                $sql = "SELECT agendar_sala.idUser 
                    FROM requisicoes_troca 
                    INNER JOIN agendar_sala ON requisicoes_troca.id_agendamento_revindicado = agendar_sala.id 
                    WHERE requisicoes_troca.id = ?";
                $stmt = $db->prepare($sql);
                if (!$stmt) throw new Exception("Erro: " . $db->error);
                $stmt->bind_param("i", $idRegistro);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                // Retorna o ID do usuário que é o dono original da sala
                return $row ? (int)$row['idUser'] : null;
            default:
                return null;
        }
    }
    public static function buscaDataAgendamento($id_agendamento): ?string
    {
        $db = Database::connects();
        $stmt = $db->prepare("SELECT dia FROM agendar_sala WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Erro: " . $db->error);
        }
        $stmt->bind_param("i", $id_agendamento);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? $row['dia'] : null;
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

        $sql = "SELECT DISTINCT $colsSql FROM $tabela $where 
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
