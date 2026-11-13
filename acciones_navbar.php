<?php
include "conn.php";

if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Error de conexión"]));
}

$accion = $_POST['accion'] ?? '';
$cajero_id = $_POST['cajero_id'] ?? 0;

if ($accion === 'cambiar_nombre') {
    $nuevo_nombre = $_POST['nombre'] ?? '';
    $stmt = $conn->prepare("UPDATE cajeros SET nombre = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_nombre, $cajero_id);
    
    if ($stmt->execute()) echo json_encode(["status" => "success"]);
    else echo json_encode(["status" => "error", "message" => $conn->error]);

} elseif ($accion === 'reset_pin') {
    $nuevo_pin = $_POST['pin'] ?? '';
    $stmt = $conn->prepare("UPDATE cajeros SET pin = ? WHERE id = ?");
    $stmt->bind_param("si", $nuevo_pin, $cajero_id);
    
    if ($stmt->execute()) echo json_encode(["status" => "success"]);
    else echo json_encode(["status" => "error", "message" => $conn->error]);
}
?>