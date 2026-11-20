<?php
require_once __DIR__ . "/../conn/conn.php";

class Impresiones
{
    private mysqli $conn;

    public function __construct(?mysqli $conexion = null)
    {
        $this->conn = $conexion ?? obtenerConexion();
    }

    public function obtenerImpresionesPorCajero(int $cajeroid): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM registro_impresiones WHERE cajero_id = ? ORDER BY creado DESC");
        $stmt->bind_param("i", $cajeroid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerUltimas10ImpresionesPorCajero(int $cajeroid): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM registro_impresiones WHERE cajero_id = ? ORDER BY creado DESC LIMIT 10");
        $stmt->bind_param("i", $cajeroid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerReimpresionesPorCajero(int $cajeroid): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM registro_impresiones WHERE cajero_id = ? AND tipo = 'reimpresion' ORDER BY creado DESC");
        $stmt->bind_param("i", $cajeroid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarImpresion(int $ventaId, int $ticketId, int $cajeroId, int $exitosa = 1): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO registro_impresiones (venta_id, ticket_id, cajero_id, tipo, exitosa)
                                      VALUES (?, ?, ?, 'original', ?)");
        $stmt->bind_param("iiii", $ventaId, $ticketId, $cajeroId, $exitosa);
        return $stmt->execute();
    }

    public function agregarReimpresion(int $ventaId, int $ticketId, int $cajeroId, int $exitosa = 1): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO registro_impresiones (venta_id, ticket_id, cajero_id, tipo, exitosa)
                                      VALUES (?, ?, ?, 'reimpresion', ?)");
        $stmt->bind_param("iiii", $ventaId, $ticketId, $cajeroId, $exitosa);
        return $stmt->execute();
    }
}
?>