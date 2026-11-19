<?php
class NavbarSP {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function getDatosCaja($caja_numero, $evento_id = 1) {
        // Obtener info básica y cajero activo
        $stmt = $this->conn->prepare("SELECT c.id as caja_id, cj.id as cajero_id, cj.nombre as cajero_nombre 
                                      FROM cajas c 
                                      LEFT JOIN cajeros cj ON c.id = cj.caja_id AND cj.activo = 1 
                                      WHERE c.numero = ? AND c.evento_id = ?");
        $stmt->bind_param("ii", $caja_numero, $evento_id);
        $stmt->execute();
        $caja = $stmt->get_result()->fetch_assoc();

        if (!$caja) return null;

        // Obtener totales por método de pago
        $stmtPagos = $this->conn->prepare("SELECT metodo_pago, SUM(total) as total, SUM(cantidad_tickets) as total_tickets 
                                           FROM ventas 
                                           WHERE caja_id = ? AND estado = 'completada' 
                                           GROUP BY metodo_pago");
        $stmtPagos->bind_param("i", $caja['caja_id']);
        $stmtPagos->execute();
        $pagos = $stmtPagos->get_result()->fetch_all(MYSQLI_ASSOC);

        $efectivo = 0;
        $tarjeta = 0;
        $tickets_emitidos = 0;
        foreach ($pagos as $p) {
            if ($p['metodo_pago'] == 'efectivo') $efectivo = $p['total'];
            if ($p['metodo_pago'] == 'tarjeta') $tarjeta = $p['total'];
            $tickets_emitidos += $p['total_tickets'];
        }

        // Últimos 10 tickets
        $stmtTickets = $this->conn->prepare("SELECT v.id AS venta_id, v.numero_operacion, v.total, v.cantidad_tickets
                                            FROM ventas v
                                            WHERE v.caja_id = ? AND v.estado = 'completada'
                                            ORDER BY v.id DESC
                                            LIMIT 10");
        $stmtTickets->bind_param("i", $caja['caja_id']);
        $stmtTickets->execute();
        $ultimos_tickets = $stmtTickets->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'cajero_id' => $caja['cajero_id'],
            'cajero_nombre' => $caja['cajero_nombre'] ?? 'Sin asignar',
            'efectivo' => $efectivo,
            'tarjeta' => $tarjeta,
            'total' => $efectivo + $tarjeta,
            'tickets_emitidos' => $tickets_emitidos,
            'ultimos_tickets' => $ultimos_tickets
        ];
    }

    public function getDatosResumen($evento_id = 1) {
        // Vasos vendidos (Producto 24 en DB)
        $stmtVasos = $this->conn->prepare("SELECT COALESCE(SUM(cantidad), 0) as vasos 
                                           FROM detalle_venta dv 
                                           JOIN ventas v ON dv.venta_id = v.id 
                                           WHERE dv.producto_evento_id = 24 AND v.evento_id = ?");
        $stmtVasos->bind_param("i", $evento_id);
        $stmtVasos->execute();
        $vasos = $stmtVasos->get_result()->fetch_assoc()['vasos'];

        // Cortesías emitidas (Precio 0)
        $stmtCortesias = $this->conn->prepare("SELECT COALESCE(SUM(cantidad), 0) as cortesias 
                                               FROM detalle_venta dv 
                                               JOIN ventas v ON dv.venta_id = v.id 
                                               WHERE dv.precio_unitario = 0 AND v.evento_id = ?");
        $stmtCortesias->bind_param("i", $evento_id);
        $stmtCortesias->execute();
        $cortesias = $stmtCortesias->get_result()->fetch_assoc()['cortesias'];

        // Producto más vendido
        $stmtTop = $this->conn->prepare("SELECT nombre_producto, SUM(cantidad) as total_vendido 
                                         FROM detalle_venta dv 
                                         JOIN ventas v ON dv.venta_id = v.id 
                                         WHERE v.evento_id = ? AND dv.precio_unitario > 0
                                         GROUP BY producto_evento_id, nombre_producto 
                                         ORDER BY total_vendido DESC LIMIT 1");
        $stmtTop->bind_param("i", $evento_id);
        $stmtTop->execute();
        $top_producto = $stmtTop->get_result()->fetch_assoc();

        // Totales generales
        $stmtTotal = $this->conn->prepare("SELECT SUM(total) as gran_total, SUM(cantidad_tickets) as total_tickets 
                                           FROM ventas WHERE evento_id = ? AND estado = 'completada'");
        $stmtTotal->bind_param("i", $evento_id);
        $stmtTotal->execute();
        $totales = $stmtTotal->get_result()->fetch_assoc();

        // Últimos 10 generales
        $stmtTicketsGen = $this->conn->prepare("SELECT t.numero_ticket, v.total, c.numero as caja_origen 
                                                FROM tickets t 
                                                JOIN ventas v ON t.venta_id = v.id 
                                                JOIN cajas c ON v.caja_id = c.id
                                                WHERE v.evento_id = ? 
                                                ORDER BY t.creado DESC LIMIT 10");
        $stmtTicketsGen->bind_param("i", $evento_id);
        $stmtTicketsGen->execute();
        $ultimos_generales = $stmtTicketsGen->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'vasos' => $vasos,
            'cortesias' => $cortesias,
            'top_producto' => $top_producto ? $top_producto['nombre_producto'] . ' (' . $top_producto['total_vendido'] . ')' : 'N/A',
            'gran_total' => $totales['gran_total'] ?? 0,
            'total_tickets' => $totales['total_tickets'] ?? 0,
            'ultimos_generales' => $ultimos_generales
        ];
    }

    public function getCajaNumeroPorCajero($cajero_id) {
        $stmt = $this->conn->prepare("SELECT c.numero 
                                      FROM cajas c 
                                      JOIN cajeros cj ON c.id = cj.caja_id 
                                      WHERE cj.id = ? LIMIT 1");
        $stmt->bind_param("i", $cajero_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? (int)$res['numero'] : null;
    }

    public function cambiarNombreCajero($cajero_id, $nuevo_nombre) {
        $stmt = $this->conn->prepare("UPDATE cajeros SET nombre = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_nombre, $cajero_id);
        return $stmt->execute();
    }

    public function resetPinCajero($cajero_id, $nuevo_pin) {
        $stmt = $this->conn->prepare("UPDATE cajeros SET pin = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_pin, $cajero_id);
        return $stmt->execute();
    }
}
?>