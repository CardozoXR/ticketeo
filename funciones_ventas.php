<?php
require_once "ventas_sp.php";
require_once "funciones_impresion.php";

class FuncionesVentas {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new VentasSP($conexion);
    }

    public function procesarCobro($caja_numero, $metodo_pago, $itemsCarrito, $evento_id = 1) {
        if (!in_array($metodo_pago, ['efectivo', 'tarjeta'], true)) {
            return ["status" => "error", "message" => "Método de pago no válido."];
        }

        if (!is_array($itemsCarrito) || count($itemsCarrito) === 0) {
            return ["status" => "error", "message" => "El carrito está vacío."];
        }

        $contexto = $this->modelo->getContextoCaja((int)$caja_numero, (int)$evento_id);
        if (!$contexto) {
            return [
                "status" => "error",
                "message" => "No se encontró caja o cajero activo en BD para la Caja #{$caja_numero}."
            ];
        }

        $itemsProcesados = [];

        try {
            foreach ($itemsCarrito as $rawItem) {
                $rawId = (string)($rawItem['id'] ?? '');
                $cantidad = (int)($rawItem['cantidad'] ?? 0);

                if ($cantidad <= 0 || !preg_match('/^(\d+)/', $rawId, $matches)) {
                    continue;
                }

                $producto_evento_id = (int)$matches[1];
                $esCortesia = str_ends_with($rawId, '-free');

                if (str_starts_with($rawId, '26x')) {
                    $prodDB = $this->modelo->obtenerOCrearProductoConsumicion((int)$evento_id);
                } else {
                    $prodDB = $this->modelo->getProductoEvento($producto_evento_id, (int)$evento_id);
                }

                if (!$prodDB) {
                    return [
                        "status" => "error",
                        "message" => "El producto #{$producto_evento_id} no existe para este evento."
                    ];
                }

                $idRealEvento = (int)$prodDB['id'];

                if ($esCortesia) {
                    $precio_unitario = 0.00;
                    if (strtoupper($prodDB['etiqueta']) === 'CONSUMO') {
                        $nombre_producto = $prodDB['nombre'] . ' (Cortesía)';
                        $etiqueta_ticket = 'CONSUMO';
                    } else {
                        $nombre_producto = 'Cortesía ' . $prodDB['nombre'];
                        $etiqueta_ticket = 'CORTESIA ' . $prodDB['etiqueta'];
                    }
                } else {
                    $precio_unitario = (float)$prodDB['precio'];
                    $nombre_producto = $prodDB['nombre'];
                    $etiqueta_ticket = $prodDB['etiqueta'];
                }

                $itemsProcesados[] = [
                    'producto_evento_id' => $idRealEvento,
                    'nombre_producto' => $nombre_producto,
                    'etiqueta_ticket' => $etiqueta_ticket,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio_unitario,
                    'subtotal' => $precio_unitario * $cantidad
                ];
            }

            if (empty($itemsProcesados)) {
                return ["status" => "error", "message" => "No hay productos válidos para procesar."];
            }

            $resultado = $this->modelo->registrarVentaCompleta(
                (int)$evento_id,
                (int)$contexto['caja_id'],
                (int)$contexto['cajero_id'],
                $metodo_pago,
                $itemsProcesados
            );

            $datosImpresion = [
                "caja_nombre" => $contexto['caja_nombre'],
                "cajero_nombre" => $contexto['cajero_nombre'],
                "numero_operacion" => $resultado['numero_operacion'],
                "tickets" => $resultado['tickets']
            ];

            $impresion = FuncionesImpresion::imprimirTicketsVenta($contexto['impresora'], $datosImpresion);

            return [
                "status" => "success",
                "caja_nombre" => $contexto['caja_nombre'],
                "cajero_nombre" => $contexto['cajero_nombre'],
                "venta_id" => $resultado['venta_id'],
                "numero_operacion" => $resultado['numero_operacion'],
                "metodo_pago" => $resultado['metodo_pago'],
                "total" => $resultado['total'],
                "cantidad_tickets" => $resultado['cantidad_tickets'],
                "tickets" => $resultado['tickets'],
                "impresion" => $impresion
            ];
        } catch (Throwable $e) {
            return [
                "status" => "error",
                "message" => $e->getMessage()
            ];
        }
    }

    public function reimprimirVenta($caja_numero, $venta_id, $evento_id = 1) {
        if ($venta_id <= 0) {
            return ["status" => "error", "message" => "ID de venta inválido."];
        }

        $contexto = $this->modelo->getContextoCaja((int)$caja_numero, (int)$evento_id);
        if (!$contexto) {
            return ["status" => "error", "message" => "Cajero activo no encontrado."];
        }

        $datos = $this->modelo->obtenerYRegistrarReimpresion((int)$venta_id, (int)$contexto['cajero_id']);
        if (!$datos) {
            return ["status" => "error", "message" => "No se encontraron tickets válidos para reimprimir."];
        }

        $datosImpresion = [
            "caja_nombre" => $contexto['caja_nombre'],
            "cajero_nombre" => $contexto['cajero_nombre'],
            "numero_operacion" => $datos['numero_operacion'],
            "tickets" => $datos['tickets']
        ];

        $impresion = FuncionesImpresion::imprimirTicketsVenta($contexto['impresora'], $datosImpresion);

        return [
            "status" => $impresion['ok'] ? "success" : "error",
            "message" => $impresion['error'] ?? "Tickets reimpresos.",
            "caja_nombre" => $contexto['caja_nombre'],
            "cajero_nombre" => $contexto['cajero_nombre'],
            "numero_operacion" => $datos['numero_operacion'],
            "tickets" => $datos['tickets'],
            "impresion" => $impresion
        ];
    }
}
?>