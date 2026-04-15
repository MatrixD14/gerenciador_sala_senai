<?php
class Calendario
{
    public static function getAgendamentos($mes, $ano): array
    {
        $connect = Database::connects();
        $sql = "SELECT agendar_sala.id as id_agendamento,
                    DAY(agendar_sala.dia) as dia_num,
                    agendar_sala.hora_inicio, 
                    agendar_sala.hora_fim,
                    turmas.turno,
                    usuario.nome as nome_usuario,
                    sala.nome as nome_sala
                FROM agendar_sala 
                inner join usuario on agendar_sala.idUser=usuario.id
                inner join sala on agendar_sala.idSala=sala.id
                inner join turmas on agendar_sala.idTurma=turmas.id
                WHERE MONTH(agendar_sala.dia) = ? AND YEAR(agendar_sala.dia) = ?";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ii", $mes, $ano);
        $stmt->execute();
        $result = $stmt->get_result();

        $eventos = [];
        while ($row = $result->fetch_assoc()) {
            $dia = (int)$row['dia_num'];
            $inicio = substr($row['hora_inicio'], 0, 5);
            $fim = substr($row['hora_fim'], 0, 5);
            $periodoFormatado = ucfirst($row['turno']) . " ($inicio - $fim)";
            $eventos[$dia][] = [
                $row['id_agendamento'],
                $row['nome_usuario'],
                $row['nome_sala'],
                $periodoFormatado
            ];
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
