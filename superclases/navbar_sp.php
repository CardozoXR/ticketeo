<?php
require_once __DIR__ . "/../conn/conn.php";

class NavbarSP
{
    private mysqli $conn;

    public function __construct(?mysqli $conexion = null)
    {
        $this->conn = $conexion ?? obtenerConexion();
    }

    public function getDatosCaja(int $caja_numero, int $evento_id = 1): ?array
    {
        $stmt = $this->conn->prepare("SELECT c.id AS caja_id, cj.id AS cajero_id, cj.nombre AS cajero_nombre 
                                      FROM cajas c 
                                      LEFT JOIN cajeros cj ON c.id = cj.caja_id AND cj.activo = 1 
                                      WHERE c.numero = ? AND c.evento_id = ? AND c.activo = 1
                                      ORDER BY cj.id DESC
                                      LIMIT 1");
        $stmt->bind_param("ii", $caja_numero, $evento_id);
        $stmt->execute();
        $caja = $stmt->get_result()->fetch_assoc();

        if (!$caja) {
            return null;
        }

        $stmtPagos = $this->conn->prepare("SELECT metodo_pago, SUM(total) AS total, SUM(cantidad_tickets) AS total_tickets 
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
            if ($p['metodo_pago'] === 'efectivo') {
                $efectivo = $p['total'];
            }
            if ($p['metodo_pago'] === 'tarjeta') {
                $tarjeta = $p['total'];
            }
            $tickets_emitidos += $p['total_tickets'];
        }

        $stmtTickets = $this->conn->prepare("SELECT v.id AS venta_id, v.numero_operacion, v.total, v.cantidad_tickets
                                            FROM ventas v
                                            WHERE v.caja_id = ? AND v.estado = 'completada'
                                            ORDER BY v.id DESC
                                            LIMIT 10");
        $stmtTickets->bind_param("i", $caja['caja_id']);
        $stmtTickets->execute();
        $ultimos_tickets = $stmtTickets->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'cajero_id'        => $caja['cajero_id'],
            'cajero_nombre'    => $caja['cajero_nombre'] ?? 'Sin asignar',
            'efectivo'         => $efectivo,
            'tarjeta'          => $tarjeta,
            'total'            => $efectivo + $tarjeta,
            'tickets_emitidos' => $tickets_emitidos,
            'ultimos_tickets'  => $ultimos_tickets
        ];
    }

    public function getDatosResumen(int $evento_id = 1): array
    {
        $stmtVasos = $this->conn->prepare("SELECT COALESCE(SUM(dv.cantidad), 0) AS vasos 
                                           FROM detalle_venta dv 
                                           JOIN ventas v ON dv.venta_id = v.id 
                                           WHERE dv.producto_evento_id = 24 AND v.evento_id = ? AND v.estado = 'completada'");
        $stmtVasos->bind_param("i", $evento_id);
        $stmtVasos->execute();
        $vasos = $stmtVasos->get_result()->fetch_assoc()['vasos'];

        $stmtCortesias = $this->conn->prepare("SELECT COALESCE(SUM(dv.cantidad), 0) AS cortesias 
                                               FROM detalle_venta dv 
                                               JOIN ventas v ON dv.venta_id = v.id 
                                               WHERE dv.precio_unitario = 0 AND v.evento_id = ? AND v.estado = 'completada'");
        $stmtCortesias->bind_param("i", $evento_id);
        $stmtCortesias->execute();
        $cortesias = $stmtCortesias->get_result()->fetch_assoc()['cortesias'];

        $stmtTop = $this->conn->prepare("SELECT dv.nombre_producto, SUM(dv.cantidad) AS total_vendido 
                                         FROM detalle_venta dv 
                                         JOIN ventas v ON dv.venta_id = v.id 
                                         WHERE v.evento_id = ? AND dv.precio_unitario > 0 AND v.estado = 'completada'
                                         GROUP BY dv.producto_evento_id, dv.nombre_producto 
                                         ORDER BY total_vendido DESC LIMIT 1");
        $stmtTop->bind_param("i", $evento_id);
        $stmtTop->execute();
        $top_producto = $stmtTop->get_result()->fetch_assoc();

        $stmtTotal = $this->conn->prepare("SELECT SUM(total) AS gran_total, SUM(cantidad_tickets) AS total_tickets 
                                           FROM ventas WHERE evento_id = ? AND estado = 'completada'");
        $stmtTotal->bind_param("i", $evento_id);
        $stmtTotal->execute();
        $totales = $stmtTotal->get_result()->fetch_assoc();

        $stmtTicketsGen = $this->conn->prepare("SELECT t.numero_ticket, v.total, c.numero AS caja_origen 
                                                FROM tickets t 
                                                JOIN ventas v ON t.venta_id = v.id 
                                                JOIN cajas c ON v.caja_id = c.id
                                                WHERE v.evento_id = ? AND v.estado = 'completada'
                                                ORDER BY t.creado DESC LIMIT 10");
        $stmtTicketsGen->bind_param("i", $evento_id);
        $stmtTicketsGen->execute();
        $ultimos_generales = $stmtTicketsGen->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'vasos'             => $vasos,
            'cortesias'         => $cortesias,
            'top_producto'      => $top_producto ? $top_producto['nombre_producto'] . ' (' . $top_producto['total_vendido'] . ')' : 'N/A',
            'gran_total'        => $totales['gran_total'] ?? 0,
            'total_tickets'     => $totales['total_tickets'] ?? 0,
            'ultimos_generales' => $ultimos_generales
        ];
    }

    public function getCajaNumeroPorCajero(int $cajero_id): ?int
    {
        $stmt = $this->conn->prepare("SELECT c.numero 
                                      FROM cajas c 
                                      JOIN cajeros cj ON c.id = cj.caja_id 
                                      WHERE cj.id = ? LIMIT 1");
        $stmt->bind_param("i", $cajero_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res ? (int)$res['numero'] : null;
    }

    public function cambiarNombreCajero(int $cajero_id, string $nuevo_nombre): bool
    {
        $stmt = $this->conn->prepare("UPDATE cajeros SET nombre = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_nombre, $cajero_id);
        return $stmt->execute();
    }

    public function resetPinCajero(int $cajero_id, string $nuevo_pin_hash): bool
    {
        $stmt = $this->conn->prepare("UPDATE cajeros SET pin = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_pin_hash, $cajero_id);
        return $stmt->execute();
    }
    public function obtenerDatosResumenImpresion(int $eventoId, ?int $cajaId = null): array
    {
        // 1. Datos del evento
        $stmtEv = $this->conn->prepare("SELECT * FROM eventos WHERE id = ? LIMIT 1");
        $stmtEv->bind_param("i", $eventoId);
        $stmtEv->execute();
        $evento = $stmtEv->get_result()->fetch_assoc() ?: [];

        // 2. Resumen de ventas (Total, Efectivo, Tarjeta)
        if ($cajaId !== null && $cajaId > 0) {
            $stmtVentas = $this->conn->prepare("
                SELECT
                    COALESCE(SUM(total), 0) AS total_ventas,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) = 'efectivo' THEN total ELSE 0 END), 0) AS total_efectivo,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) <> 'efectivo' THEN total ELSE 0 END), 0) AS total_tarjeta
                FROM ventas
                WHERE evento_id = ? AND caja_id = ? AND estado = 'completada'
            ");
            $stmtVentas->bind_param("ii", $eventoId, $cajaId);
        } else {
            $stmtVentas = $this->conn->prepare("
                SELECT
                    COALESCE(SUM(total), 0) AS total_ventas,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) = 'efectivo' THEN total ELSE 0 END), 0) AS total_efectivo,
                    COALESCE(SUM(CASE WHEN LOWER(metodo_pago) <> 'efectivo' THEN total ELSE 0 END), 0) AS total_tarjeta
                FROM ventas
                WHERE evento_id = ? AND estado = 'completada'
            ");
            $stmtVentas->bind_param("i", $eventoId);
        }
        $stmtVentas->execute();
        $ventas = $stmtVentas->get_result()->fetch_assoc();

        // 3. Resumen de tickets emitidos (Original vs. Reimpresión)
        if ($cajaId !== null && $cajaId > 0) {
            $stmtTck = $this->conn->prepare("
                SELECT
                    COUNT(ri.id) AS total_emitidos,
                    COALESCE(SUM(CASE WHEN ri.tipo = 'original' THEN 1 ELSE 0 END), 0) AS originales,
                    COALESCE(SUM(CASE WHEN ri.tipo = 'reimpresion' THEN 1 ELSE 0 END), 0) AS reimpresiones
                FROM registro_impresiones ri
                INNER JOIN ventas v ON v.id = ri.venta_id
                WHERE v.evento_id = ? AND v.caja_id = ? AND v.estado = 'completada' AND ri.exitosa = 1
            ");
            $stmtTck->bind_param("ii", $eventoId, $cajaId);
        } else {
            $stmtTck = $this->conn->prepare("
                SELECT
                    COUNT(ri.id) AS total_emitidos,
                    COALESCE(SUM(CASE WHEN ri.tipo = 'original' THEN 1 ELSE 0 END), 0) AS originales,
                    COALESCE(SUM(CASE WHEN ri.tipo = 'reimpresion' THEN 1 ELSE 0 END), 0) AS reimpresiones
                FROM registro_impresiones ri
                INNER JOIN ventas v ON v.id = ri.venta_id
                WHERE v.evento_id = ? AND v.estado = 'completada' AND ri.exitosa = 1
            ");
            $stmtTck->bind_param("i", $eventoId);
        }
        $stmtTck->execute();
        $tickets = $stmtTck->get_result()->fetch_assoc();

        return [
            'evento' => $evento,
            'ventas' => [
                'total'    => (float)$ventas['total_ventas'],
                'efectivo' => (float)$ventas['total_efectivo'],
                'tarjeta'  => (float)$ventas['total_tarjeta'],
            ],
            'tickets' => [
                'total_emitidos' => (int)$tickets['total_emitidos'],
                'original'       => (int)$tickets['originales'],
                'reimpresion'    => (int)$tickets['reimpresiones'],
            ],
        ];
    }
}
?>