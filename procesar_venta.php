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

    include_once "conn.php";
    require_once "funciones_ventas.php";

    if (!isset($conn) || !$conn) {
        throw new Exception("No hay conexión con la base de datos.");
    }

    $payload = json_decode(file_get_contents('php://input'), true);
    if (!is_array($payload)) {
        ob_end_clean();
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Datos JSON inválidos."]);
        exit;
    }

    $controlador = new FuncionesVentas($conn);
    $accion = $payload['accion'] ?? 'registrar';
    $cajaActual = (int)$_SESSION['caja'];

    if ($accion === 'reimprimir') {
        $venta_id = (int)($payload['venta_id'] ?? 0);
        $respuesta = $controlador->reimprimirVenta($cajaActual, $venta_id, 1);
    } else {
        $metodo_pago = $payload['metodo_pago'] ?? '';
        $items = $payload['items'] ?? [];
        $respuesta = $controlador->procesarCobro($cajaActual, $metodo_pago, $items, 1);
    }

    $json = json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) {
        $json = json_encode([
            "status" => "error",
            "message" => "Error codificando JSON: " . json_last_error_msg()
        ]);
    }

    ob_end_clean();
    echo $json;
} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error interno: " . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
}
?>