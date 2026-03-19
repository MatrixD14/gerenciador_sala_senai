<?php
class TabelaCleanup
{
    public static function autoCleanupTableAgendamento($dias = 364)
    {
        $db = Database::connects();
        $dataCorte = date('Y-m-d', strtotime("-$dias days"));
        $stmt = $db->prepare("delete from agendar_sala WHERE dia < ?");
        $stmt->bind_param("s", $dataCorte);
        $stmt->execute();
        $stmt->close();
    }
    public static function autoCleanupReivindicacao($dias = 364)
    {
        $db = Database::connects();
        $dataCorte = date('Y-m-d', strtotime("-$dias days"));
        $stmt = $db->prepare("delete from revindicados WHERE data_envio < ?");
        $stmt->bind_param("s", $dataCorte);
        $stmt->execute();
        $stmt->close();
    }
}
