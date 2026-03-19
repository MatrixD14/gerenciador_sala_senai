<?php
class Calendario
{
    public static function getAgendamentos($mes, $ano): array
    {
        $connect = Database::connects();
        $sql = "SELECT usuario.id as id_usuario, DAY(agendar_sala.dia) as dia_num,periodo, usuario.name as nome_usuario, sala.name as nome_sala
                FROM agendar_sala 
                inner join usuario on agendar_sala.idUser=usuario.id
                inner join sala on agendar_sala.idSala=sala.id
                WHERE MONTH(agendar_sala.dia) = ? AND YEAR(agendar_sala.dia) = ?";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ii", $mes, $ano);
        $stmt->execute();
        $result = $stmt->get_result();

        $eventos = [];
        while ($row = $result->fetch_assoc()) {
            $dia = (int)$row['dia_num'];
            $eventos[$dia][] = [$row['id_usuario'], $row['nome_usuario'], $row['nome_sala'], $row['periodo']];
        }
        return $eventos;
    }
    public static function datasAgendamentos()
    {
        header('Content-Type: application/json');
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
        $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
        $agendamentos = self::getAgendamentos($mes, $ano);
        echo json_encode($agendamentos);
        exit;
    }
}
