<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['caja'])) {
        ob_end_clean();
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Sesión expirada. Iniciá caja nuevamente."]);
        exit;
    }

    require_once __DIR__ . "/../interfaces/ventas_interfaz.php";

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Datos JSON inválidos."]);
        exit;
    }

    $controlador = new FuncionesVentas();
    $accion      = $payload['accion'] ?? 'registrar';
    $cajaActual  = (int)$_SESSION['caja'];
    $eventoId    = isset($_SESSION['evento_id']) ? (int)$_SESSION['evento_id'] : null;

    if ($accion === 'reimprimir') {
        $venta_id  = (int)($payload['venta_id'] ?? 0);
        $respuesta = $controlador->reimprimirVenta($cajaActual, $venta_id, $eventoId);
    } else {
        $metodo_pago = (string)($payload['metodo_pago'] ?? '');
        $items       = is_array($payload['items'] ?? null) ? $payload['items'] : [];
        $respuesta   = $controlador->procesarCobro($cajaActual, $metodo_pago, $items, $eventoId);
    }

    $json = json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        $json = json_encode([
            "status"  => "error",
            "message" => "Error codificando JSON: " . json_last_error_msg()
        ]);
    }

    ob_end_clean();
    echo $json;
} catch (Throwable $e) {
    error_log("Error en endpoints/procesar_venta.php: " . $e->getMessage());
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Error interno del servidor."
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}
?>