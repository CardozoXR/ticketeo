<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['caja'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Sesión expirada. Iniciá caja nuevamente."], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . "/../interfaces/navbar_interfaz.php";

$controlador = new FuncionesNavbar();
$accion      = $_POST['accion'] ?? '';
$cajero_id   = (int)($_POST['cajero_id'] ?? 0);

if ($accion === 'cambiar_nombre') {
    echo json_encode($controlador->actualizarNombre($cajero_id, $_POST['nombre'] ?? ''), JSON_UNESCAPED_UNICODE);
} elseif ($accion === 'reset_pin') {
    echo json_encode($controlador->actualizarPin($cajero_id, $_POST['pin'] ?? ''), JSON_UNESCAPED_UNICODE);
} elseif ($accion === 'imprimir_resumen') {
    $cajaFiltro = isset($_POST['caja_filtro']) && $_POST['caja_filtro'] !== ''
        ? (int)$_POST['caja_filtro']
        : null;

    $eventoId     = isset($_SESSION['evento_id']) ? (int)$_SESSION['evento_id'] : null;
    $cajaSesionId = (int)($_SESSION['caja'] ?? 0);
    $cajeroNombre = (string)($_SESSION['nombre'] ?? 'Cajero');
    $impresora    = $_SESSION['impresora'] ?? null;

    $respuesta = $controlador->procesarImprimirResumen(
        $eventoId,
        $cajaSesionId,
        $cajeroNombre,
        $impresora,
        $cajaFiltro
    );
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Acción no válida."], JSON_UNESCAPED_UNICODE);
}
?>