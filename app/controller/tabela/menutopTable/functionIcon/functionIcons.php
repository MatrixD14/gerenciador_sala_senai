<?php
class FucntIcons
{
    public static function delete($table, $id): bool
    {
        $connect = Database::connects();
        $tmp = $connect->prepare("delete from $table  where id=?");
        $tmp->bind_param("i", $id);
        $tmp->execute();
        $tmp->close();
        if ($tmp->affected_rows > 0)
            return true;
        return true;
    }
}
