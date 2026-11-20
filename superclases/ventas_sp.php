<?php
require_once __DIR__ . "/../conn/conn.php";

class Ventas
{
    private mysqli $conn;

    public function __construct(?mysqli $conexion = null)
    {
        $this->conn = $conexion ?? obtenerConexion();
    }

    public function registrarVentaCompleta(
        int $eventoId,
        string $fechaEvento,
        int $cajaId,
        int $cajeroId,
        string $metodoPago,
        array $itemsProcesados
    ): array {
        $this->conn->begin_transaction();

        try {
            $prefijoEvento = 1000 + $eventoId;
            $fechaDdMm     = date('dm', strtotime($fechaEvento) ?: time());

            $stmtOp = $this->conn->prepare("SELECT COALESCE(MAX(numero_operacion), 0) + 1 AS siguiente
                                            FROM ventas
                                            WHERE evento_id = ?
                                            FOR UPDATE");
            $stmtOp->bind_param("i", $eventoId);
            $stmtOp->execute();
            $numeroOperacion = (int)$stmtOp->get_result()->fetch_assoc()['siguiente'];

            $totalVenta      = 0.00;
            $cantidadTickets = 0;
            foreach ($itemsProcesados as $item) {
                $totalVenta      += $item['subtotal'];
                $cantidadTickets += $item['cantidad'];
            }

            $stmtVenta = $this->conn->prepare("INSERT INTO ventas
                (evento_id, caja_id, cajero_id, numero_operacion, metodo_pago, total, cantidad_tickets, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'completada')");
            $stmtVenta->bind_param(
                "iiiisdi",
                $eventoId,
                $cajaId,
                $cajeroId,
                $numeroOperacion,
                $metodoPago,
                $totalVenta,
                $cantidadTickets
            );
            $stmtVenta->execute();
            $ventaId = (int)$this->conn->insert_id;

            $stmtDetalle = $this->conn->prepare("INSERT INTO detalle_venta
                (venta_id, producto_evento_id, nombre_producto, etiqueta_ticket, cantidad, precio_unitario, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmtTicket = $this->conn->prepare("INSERT INTO tickets
                (venta_id, detalle_venta_id, numero_ticket, codigo_qr, estado)
                VALUES (?, ?, ?, ?, 'valido')");

            $stmtUpdateQr = $this->conn->prepare("UPDATE tickets SET codigo_qr = ? WHERE id = ?");

            $stmtImp = $this->conn->prepare("INSERT INTO registro_impresiones
                (venta_id, ticket_id, cajero_id, tipo, exitosa)
                VALUES (?, ?, ?, 'original', 1)");

            $ticketsGenerados = [];
            $correlativo      = 1;

            foreach ($itemsProcesados as $item) {
                $stmtDetalle->bind_param(
                    "iissidd",
                    $ventaId,
                    $item['producto_evento_id'],
                    $item['nombre_producto'],
                    $item['etiqueta_ticket'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['subtotal']
                );
                $stmtDetalle->execute();
                $detalleVentaId = (int)$this->conn->insert_id;

                for ($i = 0; $i < $item['cantidad']; $i++) {
                    $numeroTicket = sprintf("T%d-%d-%03d", $eventoId, $ventaId, $correlativo);
                    $tmpQr        = 'TMP-' . bin2hex(random_bytes(8));

                    $stmtTicket->bind_param("iiss", $ventaId, $detalleVentaId, $numeroTicket, $tmpQr);
                    $stmtTicket->execute();
                    $ticketId = (int)$this->conn->insert_id;

                    // Formato QR: (1000 + evento_id) + ddmm + producto_id (2 dígitos) + ticket_id
                    $codigoQr = sprintf(
                        "%d%s%02d%d",
                        $prefijoEvento,
                        $fechaDdMm,
                        (int)$item['producto_id'],
                        $ticketId
                    );

                    $stmtUpdateQr->bind_param("si", $codigoQr, $ticketId);
                    $stmtUpdateQr->execute();

                    $stmtImp->bind_param("iii", $ventaId, $ticketId, $cajeroId);
                    $stmtImp->execute();

                    $ticketsGenerados[] = [
                        'ticket_id'     => $ticketId,
                        'numero_ticket' => $numeroTicket,
                        'codigo_qr'     => $codigoQr,
                        'etiqueta'      => $item['etiqueta_ticket'],
                        'precio'        => $item['precio_unitario']
                    ];

                    $correlativo++;
                }
            }

            $this->conn->commit();

            return [
                'venta_id'         => $ventaId,
                'numero_operacion' => $numeroOperacion,
                'metodo_pago'      => $metodoPago,
                'total'            => $totalVenta,
                'cantidad_tickets' => $cantidadTickets,
                'tickets'          => $ticketsGenerados
            ];
        } catch (Throwable $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
}
?>