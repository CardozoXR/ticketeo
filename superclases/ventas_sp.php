<?php
class Ventas
{
    private $conn;

    public function __construct($conexion)
    {
        $this->conn = $conexion;
    }

    public function getContextoCaja($caja_numero, $evento_id = 1)
    {
        $stmt = $this->conn->prepare("SELECT c.id AS caja_id, c.nombre AS caja_nombre, c.impresora,
                                             cj.id AS cajero_id, cj.nombre AS cajero_nombre
                                      FROM cajas c
                                      JOIN cajeros cj ON c.id = cj.caja_id AND cj.activo = 1
                                      WHERE c.numero = ? AND c.evento_id = ? AND c.activo = 1
                                      LIMIT 1");
        $stmt->bind_param("ii", $caja_numero, $evento_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getProductoEvento($producto_evento_id, $evento_id = 1)
    {
        $stmt = $this->conn->prepare("SELECT pe.id, pe.precio, pe.etiqueta, p.nombre
                                      FROM productos_evento pe
                                      JOIN productos p ON pe.producto_id = p.id
                                      WHERE pe.id = ? AND pe.evento_id = ?
                                      LIMIT 1");
        $stmt->bind_param("ii", $producto_evento_id, $evento_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerFechaEvento($eventoid)
    {
        $sql = "SELECT fecha FROM eventos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $eventoid);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    public function obtenerOCrearProductoConsumicion($evento_id = 1)
    {
        $stmt = $this->conn->prepare("SELECT pe.id, pe.precio, pe.etiqueta, p.nombre
                                      FROM productos_evento pe
                                      JOIN productos p ON pe.producto_id = p.id
                                      WHERE pe.evento_id = ? AND (pe.id = 26 OR p.nombre = 'Consumición')
                                      LIMIT 1");
        $stmt->bind_param("i", $evento_id);
        $stmt->execute();
        $existente = $stmt->get_result()->fetch_assoc();
        if ($existente) {
            return $existente;
        }

        $stmtProd = $this->conn->prepare("SELECT id FROM productos WHERE nombre = 'Consumición' LIMIT 1");
        $stmtProd->execute();
        $prod = $stmtProd->get_result()->fetch_assoc();

        if ($prod) {
            $producto_id = (int) $prod['id'];
        } else {
            $stmtInsProd = $this->conn->prepare("INSERT INTO productos (categoria_id, nombre, img, activo)
                                                 VALUES (4, 'Consumición', 'imgs/consumicion.png', 1)");
            if (!$stmtInsProd->execute()) {
                throw new Exception("No se pudo crear el producto Consumición: " . $stmtInsProd->error);
            }
            $producto_id = (int) $this->conn->insert_id;
        }

        $stmtInsPe = $this->conn->prepare("INSERT INTO productos_evento (evento_id, producto_id, precio, etiqueta, orden, disponible)
                                           VALUES (?, ?, 0.00, 'CONSUMO', 1, 1)");
        $stmtInsPe->bind_param("ii", $evento_id, $producto_id);
        if (!$stmtInsPe->execute()) {
            throw new Exception("No se pudo vincular Consumición al evento: " . $stmtInsPe->error);
        }

        return [
            'id' => (int) $this->conn->insert_id,
            'precio' => 0.00,
            'etiqueta' => 'CONSUMO',
            'nombre' => 'Consumición'
        ];
    }

    public function registrarVentaCompleta($evento_id, $caja_id, $cajero_id, $metodo_pago, $itemsProcesados)
    {
        $this->conn->begin_transaction();

        try {
            $stmtOp = $this->conn->prepare("SELECT COALESCE(MAX(numero_operacion), 0) + 1 AS siguiente
                                            FROM ventas
                                            WHERE evento_id = ?
                                            FOR UPDATE");
            if (!$stmtOp) {
                throw new Exception("Error preparando operación: " . $this->conn->error);
            }
            $stmtOp->bind_param("i", $evento_id);
            if (!$stmtOp->execute()) {
                throw new Exception("Error calculando operación: " . $stmtOp->error);
            }
            $numero_operacion = (int) $stmtOp->get_result()->fetch_assoc()['siguiente'];

            $total_venta = 0.00;
            $cantidad_tickets = 0;
            foreach ($itemsProcesados as $item) {
                $total_venta += $item['subtotal'];
                $cantidad_tickets += $item['cantidad'];
            }

            $stmtVenta = $this->conn->prepare("INSERT INTO ventas 
                (evento_id, caja_id, cajero_id, numero_operacion, metodo_pago, total, cantidad_tickets, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'completada')");
            if (!$stmtVenta) {
                throw new Exception("Error preparando venta: " . $this->conn->error);
            }
            $stmtVenta->bind_param(
                "iiiisdi",
                $evento_id,
                $caja_id,
                $cajero_id,
                $numero_operacion,
                $metodo_pago,
                $total_venta,
                $cantidad_tickets
            );
            if (!$stmtVenta->execute()) {
                throw new Exception("Error insertando venta: " . $stmtVenta->error);
            }
            $venta_id = (int) $this->conn->insert_id;

            $stmtDetalle = $this->conn->prepare("INSERT INTO detalle_venta 
                (venta_id, producto_evento_id, nombre_producto, etiqueta_ticket, cantidad, precio_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmtDetalle) {
                throw new Exception("Error preparando detalle_venta: " . $this->conn->error);
            }

            $stmtTicket = $this->conn->prepare("INSERT INTO tickets 
                (venta_id, detalle_venta_id, numero_ticket, codigo_qr, estado)
                VALUES (?, ?, ?, ?, 'valido')");
            if (!$stmtTicket) {
                throw new Exception("Error preparando tickets: " . $this->conn->error);
            }

            $stmtImp = $this->conn->prepare("INSERT INTO registro_impresiones 
                (venta_id, ticket_id, cajero_id, tipo, exitosa)
                VALUES (?, ?, ?, 'original', 1)");
            if (!$stmtImp) {
                throw new Exception("Error preparando registro_impresiones: " . $this->conn->error);
            }

            $tickets_generados = [];
            $correlativo = 1;

            $fechaDdmm = date('dm', strtotime($this->obtenerFechaEvento($evento_id)['fecha']));

            foreach ($itemsProcesados as $item) {
                $stmtDetalle->bind_param(
                    "iissidd",
                    $venta_id,
                    $item['producto_evento_id'],
                    $item['nombre_producto'],
                    $item['etiqueta_ticket'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['subtotal']
                );
                if (!$stmtDetalle->execute()) {
                    throw new Exception("Error insertando detalle ({$item['nombre_producto']}): " . $stmtDetalle->error);
                }
                $detalle_venta_id = (int) $this->conn->insert_id;

                for ($i = 0; $i < $item['cantidad']; $i++) {
                    $numero_ticket = sprintf("T%d-%d-%03d", $evento_id, $venta_id, $correlativo);

                    $codigo_qr = sprintf(
                        '%04d%s%03d%06d%03d',
                        1000 + $evento_id,
                        $fechaDdmm,
                        $item['producto_evento_id'],
                        $detalle_venta_id,
                        $i
                    );

                    $stmtTicket->bind_param("iiss", $venta_id, $detalle_venta_id, $numero_ticket, $codigo_qr);
                    if (!$stmtTicket->execute()) {
                        throw new Exception("Error insertando ticket: " . $stmtTicket->error);
                    }
                    $ticket_id = (int) $this->conn->insert_id;

                    $stmtImp->bind_param("iii", $venta_id, $ticket_id, $cajero_id);
                    if (!$stmtImp->execute()) {
                        throw new Exception("Error registrando impresión: " . $stmtImp->error);
                    }

                    $tickets_generados[] = [
                        'ticket_id' => $ticket_id,
                        'numero_ticket' => $numero_ticket,
                        'codigo_qr' => $codigo_qr,
                        'etiqueta' => $item['etiqueta_ticket'],
                        'precio' => $item['precio_unitario']
                    ];

                    $correlativo++;
                }
            }

            $this->conn->commit();

            return [
                'venta_id' => $venta_id,
                'numero_operacion' => $numero_operacion,
                'metodo_pago' => $metodo_pago,
                'total' => $total_venta,
                'cantidad_tickets' => $cantidad_tickets,
                'tickets' => $tickets_generados
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    public function obtenerYRegistrarReimpresion($venta_id, $cajero_id)
    {
        $stmt = $this->conn->prepare("SELECT t.id AS ticket_id, t.numero_ticket, t.codigo_qr,
                                             dv.etiqueta_ticket AS etiqueta, dv.precio_unitario AS precio,
                                             v.numero_operacion
                                      FROM tickets t
                                      JOIN detalle_venta dv ON t.detalle_venta_id = dv.id
                                      JOIN ventas v ON t.venta_id = v.id
                                      WHERE t.venta_id = ? AND t.estado = 'valido'
                                      ORDER BY t.id ASC");
        $stmt->bind_param("i", $venta_id);
        $stmt->execute();
        $filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($filas)) {
            return null;
        }

        $stmtImp = $this->conn->prepare("INSERT INTO registro_impresiones 
            (venta_id, ticket_id, cajero_id, tipo, exitosa)
            VALUES (?, ?, ?, 'reimpresion', 1)");

        foreach ($filas as $f) {
            $ticket_id = (int) $f['ticket_id'];
            $stmtImp->bind_param("iii", $venta_id, $ticket_id, $cajero_id);
            $stmtImp->execute();
        }

        return [
            'numero_operacion' => (int) $filas[0]['numero_operacion'],
            'tickets' => $filas
        ];
    }
}
?>