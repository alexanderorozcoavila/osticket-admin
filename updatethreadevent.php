<?php
$link = mysqli_connect("localhost", "administrador", "BtCFfa~G5n=9", "nous00_os1");
                        
if ($link == false) {
    die("ERROR: Could not connect. "
                .mysqli_connect_error());
}

$sql = "SELECT id, thread_id, timestamp as hora, DATE_ADD(timestamp, INTERVAL 5 MINUTE) as limite, NOW() as hora_actual FROM `os_thread_event` WHERE `data` = 'notedit'";
$res = mysqli_query($link, $sql);
while ($row = mysqli_fetch_array($res)) {
    $hora_thread = strtotime($row['hora']);
    $hora_limite = strtotime($row['limite']);
    $hora_actual = strtotime($row['hora_actual']);
    if($hora_actual >= $hora_limite){
        $sql2 ="DELETE FROM `os_thread_event` WHERE `os_thread_event`.`id` = ".$row['id'];
        $resultado = mysqli_query($link, $sql2);
        if($resultado){
            echo $row['thread_id'].": liberado...";
            echo "\n";
        }else{
            echo $row['thread_id'].": en espera...";
            echo "\n";
        }
    }else{
        echo $row['thread_id'].": en espera...";
        echo "\n";
    }

}

?>