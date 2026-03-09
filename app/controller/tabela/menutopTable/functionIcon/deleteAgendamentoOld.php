<?php
class TabelaCleanup
{
    public static function autoCleanup($dias = 364)
    {
        $db = Database::connects();
        $dataCorte = date('Y-m-d', strtotime("-$dias days"));
        $stmt = $db->prepare("DELETE FROM agendar_sala WHERE dia < ?");
        $stmt->bind_param("s", $dataCorte);
        $stmt->execute();
        $stmt->close();
    }
}
