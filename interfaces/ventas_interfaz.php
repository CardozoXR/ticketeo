<?php
require_once __DIR__ . "/../superclases/ventas_sp.php";
require_once __DIR__ . "/eventos_interfaz.php";
require_once __DIR__ . "/caja_interfaz.php";
require_once __DIR__ . "/productos_interfaz.php";
require_once __DIR__ . "/tickets_interfaz.php";
require_once __DIR__ . "/registroImpresiones_interfaz.php";
require_once __DIR__ . "/impresion_interfaz.php";

class FuncionesVentas
{
    private Ventas $modelo;

    public function __construct(?mysqli $conexion = null)
    {
        $this->modelo = new Ventas($conexion);
    }

    public function procesarCobro(int $cajaNumero, string $metodoPago, array $itemsCarrito, ?int $eventoId = null): array
    {
        if (!in_array($metodoPago, ['efectivo', 'tarjeta'], true)) {
            return ["status" => "error", "message" => "Método de pago no válido."];
        }

        if (count($itemsCarrito) === 0) {
            return ["status" => "error", "message" => "El carrito está vacío."];
        }

        $evento = $eventoId ? obtenerEventoPorID($eventoId) : obtenerEventoActivo();
        if (!$evento) {
            return ["status" => "error", "message" => "No hay ningún evento activo."];
        }
        $eventoIdReal = (int)$evento['id'];

        $contexto = obtenerContextoCaja($cajaNumero, $eventoIdReal);
        if (!$contexto) {
            return [
                "status"  => "error",
                "message" => "No se encontró caja o cajero activo para la Caja #{$cajaNumero}."
            ];
        }

        $itemsProcesados = [];

        try {
            foreach ($itemsCarrito as $rawItem) {
                $rawId    = (string)($rawItem['id'] ?? '');
                $cantidad = (int)($rawItem['cantidad'] ?? 0);

                if ($cantidad <= 0 || $cantidad > 200 || !preg_match('/^(\d+)/', $rawId, $matches)) {
                    continue;
                }

                $productoEventoId = (int)$matches[1];
                $esCortesia       = str_ends_with($rawId, '-free');

                if (str_starts_with($rawId, '26x')) {
                    $prodDB = obtenerProductoConsumicionEvento($eventoIdReal);
                } else {
                    $prodDB = obtenerProductoEventoParaVenta($productoEventoId, $eventoIdReal);
                }

                if (!$prodDB) {
                    return [
                        "status"  => "error",
                        "message" => "El producto #{$productoEventoId} no está disponible para este evento."
                    ];
                }

                $idRealEvento   = (int)$prodDB['id'];
                $idRealProducto = (int)$prodDB['producto_id'];

                if ($esCortesia) {
                    $precioUnitario = 0.00;
                    if (strtoupper($prodDB['etiqueta']) === 'CONSUMO') {
                        $nombreProducto = $prodDB['nombre'] . ' (Cortesía)';
                        $etiquetaTicket = 'CONSUMO';
                    } else {
                        $nombreProducto = 'Cortesía ' . $prodDB['nombre'];
                        $etiquetaTicket = 'CORTESIA ' . $prodDB['etiqueta'];
                    }
                } else {
                    $precioUnitario = (float)$prodDB['precio'];
                    $nombreProducto = $prodDB['nombre'];
                    $etiquetaTicket = $prodDB['etiqueta'];
                }

                $itemsProcesados[] = [
                    'producto_evento_id' => $idRealEvento,
                    'producto_id'        => $idRealProducto,
                    'nombre_producto'    => $nombreProducto,
                    'etiqueta_ticket'    => $etiquetaTicket,
                    'cantidad'           => $cantidad,
                    'precio_unitario'    => $precioUnitario,
                    'subtotal'           => $precioUnitario * $cantidad
                ];
            }

            if (empty($itemsProcesados)) {
                return ["status" => "error", "message" => "No hay productos válidos para procesar."];
            }

            $resultado = $this->modelo->registrarVentaCompleta(
                $eventoIdReal,
                $evento['fecha'],
                (int)$contexto['caja_id'],
                (int)$contexto['cajero_id'],
                $metodoPago,
                $itemsProcesados
            );

            $datosImpresion = [
                "evento_nombre"    => $evento['nombre'],
                "caja_nombre"      => $contexto['caja_nombre'],
                "cajero_nombre"    => $contexto['cajero_nombre'],
                "numero_operacion" => $resultado['numero_operacion'],
                "tickets"          => $resultado['tickets']
            ];

            $impresion = FuncionesImpresion::imprimirTicketsVenta($contexto['impresora'], $datosImpresion);

            return [
                "status"           => "success",
                "evento_nombre"    => $evento['nombre'],
                "caja_nombre"      => $contexto['caja_nombre'],
                "cajero_nombre"    => $contexto['cajero_nombre'],
                "venta_id"         => $resultado['venta_id'],
                "numero_operacion" => $resultado['numero_operacion'],
                "metodo_pago"      => $resultado['metodo_pago'],
                "total"            => $resultado['total'],
                "cantidad_tickets" => $resultado['cantidad_tickets'],
                "tickets"          => $resultado['tickets'],
                "impresion"        => $impresion
            ];
        } catch (Throwable $e) {
            error_log("Error en procesarCobro: " . $e->getMessage());
            return [
                "status"  => "error",
                "message" => "No se pudo registrar la venta. Intentá nuevamente."
            ];
        }
    }

    public function reimprimirVenta(
    int $cajaNumero,
    int $ventaId,
    ?int $eventoId = null,
    string $impresora = ''
): array
{
    if ($ventaId <= 0) {
        return ["status" => "error", "message" => "ID de venta inválido."];
    }

    $evento = $eventoId ? obtenerEventoPorID($eventoId) : obtenerEventoActivo();

    if (!$evento) {
        return ["status" => "error", "message" => "Evento activo no encontrado."];
    }

    $contexto = obtenerContextoCaja($cajaNumero, (int)$evento['id']);

    if (!$contexto) {
        return ["status" => "error", "message" => "Cajero activo no encontrado."];
    }

    $tickets = obtenerTicketsDetallePorVenta($ventaId);

    if (empty($tickets)) {
        return ["status" => "error", "message" => "No se encontraron tickets válidos para reimprimir."];
    }

    foreach ($tickets as $t) {
        agregarReimpresion(
            $ventaId,
            (int)$t['ticket_id'],
            (int)$contexto['cajero_id'],
            1
        );
    }

    $numeroOperacion = (int)$tickets[0]['numero_operacion'];

    $datosImpresion = [
        "evento_nombre"    => $evento['nombre'],
        "caja_nombre"      => $contexto['caja_nombre'],
        "cajero_nombre"    => $contexto['cajero_nombre'],
        "numero_operacion" => $numeroOperacion,
        "tickets"          => $tickets
    ];

    // Si no se especifica impresora, utiliza la de la caja.
    $impresoraFinal = ($impresora !== '')
        ? $impresora
        : $contexto['impresora'];

    $impresion = FuncionesImpresion::imprimirTicketsVenta(
        $impresoraFinal,
        $datosImpresion
    );

    return [
        "status"           => $impresion['ok'] ? "success" : "error",
        "message"          => $impresion['error'] ?? "Tickets reimpresos.",
        "evento_nombre"    => $evento['nombre'],
        "caja_nombre"      => $contexto['caja_nombre'],
        "cajero_nombre"    => $contexto['cajero_nombre'],
        "numero_operacion" => $numeroOperacion,
        "tickets"          => $tickets,
        "impresion"        => $impresion
    ];
}
}
?>