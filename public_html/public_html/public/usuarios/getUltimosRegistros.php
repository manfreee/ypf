<?php
include($_SERVER['DOCUMENT_ROOT'] . '/conexion.php');

$id_modulo = intval($_GET['id_modulo'] ?? 0);
$limite = intval($_GET['limite'] ?? 20);

if($id_modulo > 0){
    $res = mysqli_query($conexion, "
        SELECT fecha_hora, energia
        FROM datos
        WHERE id_modulo=$id_modulo
        ORDER BY fecha_hora DESC
        LIMIT $limite
    ");

    $datos = [];
    if($res && mysqli_num_rows($res) > 0){
        while($fila = mysqli_fetch_assoc($res)){
            $datos[] = [
                "fecha" => $fila['fecha_hora'],
                "hora"  => date("H:i:s", strtotime($fila['fecha_hora'])),
                "valor" => floatval($fila['energia'])*1000 // convertir a Wh
            ];
        }
    }
    $datos = array_reverse($datos);
    echo json_encode(["datos"=>$datos]);
    exit;
}

echo json_encode(["datos"=>[]]);
