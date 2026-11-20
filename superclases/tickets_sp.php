<?php
require_once __DIR__ . "/../conn/conn.php";

class Tickets
{
    private mysqli $conn;

    public function __construct(?mysqli $conexion = null)
    {
        $this->conn = $conexion ?? obtenerConexion();
    }

    public function obtenerTodosLosTickets(): array
    {
        $resultado = $this->conn->query("SELECT * FROM tickets ORDER BY id DESC");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerTicketsPorVenta(int $ventaId): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM tickets WHERE venta_id = ? ORDER BY id ASC");
        $stmt->bind_param("i", $ventaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerTicketsDetallePorVenta(int $ventaId): array
    {
        $stmt = $this->conn->prepare("SELECT t.id AS ticket_id, t.numero_ticket, t.codigo_qr, t.estado,
                                             dv.etiqueta_ticket AS etiqueta, dv.nombre_producto,
                                             dv.precio_unitario AS precio,
                                             v.numero_operacion, v.evento_id,
                                             c.nombre AS caja_nombre, cj.nombre AS cajero_nombre
                                      FROM tickets t
                                      JOIN detalle_venta dv ON t.detalle_venta_id = dv.id
                                      JOIN ventas v ON t.venta_id = v.id
                                      JOIN cajas c ON v.caja_id = c.id
                                      JOIN cajeros cj ON v.cajero_id = cj.id
                                      WHERE t.venta_id = ? AND t.estado = 'valido'
                                      ORDER BY t.id ASC");
        $stmt->bind_param("i", $ventaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>