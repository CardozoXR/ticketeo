<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
include_once "conn.php";
require_once "funciones_navbar.php";

if (!$conn) {
    die(json_encode(["status" => "error", "message" => "Error de conexión"]));
}

$controlador = new FuncionesNavbar($conn);
$accion = $_POST['accion'] ?? '';
$cajero_id = (int)($_POST['cajero_id'] ?? 0);

if ($accion === 'cambiar_nombre') {
    echo json_encode($controlador->actualizarNombre($cajero_id, $_POST['nombre'] ?? ''));
} elseif ($accion === 'reset_pin') {
    echo json_encode($controlador->actualizarPin($cajero_id, $_POST['pin'] ?? ''));
} else {
    echo json_encode(["status" => "error", "message" => "Acción no válida"]);
}
?>