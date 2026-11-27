<?php
require_once __DIR__ . "/../conn/conn.php";

class Cajas
{
    private mysqli $conn;

    public function __construct(?mysqli $conexion = null)
    {
        $this->conn = $conexion ?? obtenerConexion();
    }

    public function obtenerTodasLasCajas(): array
    {
        $resultado = $this->conn->query("SELECT * FROM cajas ORDER BY evento_id DESC, numero ASC");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCajaPorID(int $cajaid): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM cajas WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $cajaid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function obtenerCajaPorNumero(int $numeroCaja, int $eventoId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM cajas WHERE numero = ? AND evento_id = ? AND activo = 1 LIMIT 1");
        $stmt->bind_param("ii", $numeroCaja, $eventoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function obtenerContextoCaja(int $numeroCaja, int $eventoId): ?array
    {
        $stmt = $this->conn->prepare("SELECT c.id AS caja_id, c.numero AS caja_numero, c.nombre AS caja_nombre, c.impresora,
                                             cj.id AS cajero_id, cj.nombre AS cajero_nombre
                                      FROM cajas c
                                      JOIN cajeros cj ON c.id = cj.caja_id AND cj.activo = 1
                                      WHERE c.numero = ? AND c.evento_id = ? AND c.activo = 1
                                      ORDER BY cj.id DESC
                                      LIMIT 1");
        $stmt->bind_param("ii", $numeroCaja, $eventoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function obtenerCajasPorEvento(int $eventoid): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM cajas WHERE evento_id = ? ORDER BY numero ASC");
        $stmt->bind_param("i", $eventoid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCajasActivas(): array
    {
        $resultado = $this->conn->query("SELECT * FROM cajas WHERE activo = 1 ORDER BY numero ASC");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCajasActivasPorEvento(int $eventoid, $activo = 1){
        $stmt = $this->conn->prepare("SELECT * FROM cajas WHERE evento_id = ? AND activo = ?");
        $stmt->bind_param("ii", $eventoid, $activo);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerImpresoraCaja(int $numeroCaja){
        $activo = 1;

        $stmt = $this->conn->prepare("SELECT impresora FROM cajas WHERE numero = ? AND activo = ?");
        $stmt->bind_param("ii", $numeroCaja, $activo);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarCaja(int $eventoid, int $numero, string $nombre, string $impresora, int $activo): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO cajas (evento_id, numero, nombre, impresora, activo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $eventoid, $numero, $nombre, $impresora, $activo);
        return $stmt->execute();
    }

    public function editarCaja(int $cajaid, int $eventoid, int $numero, string $nombre, string $impresora, int $activo): bool
    {
        $stmt = $this->conn->prepare("UPDATE cajas SET evento_id = ?, numero = ?, nombre = ?, impresora = ?, activo = ? WHERE id = ?");
        $stmt->bind_param("iissii", $eventoid, $numero, $nombre, $impresora, $activo, $cajaid);
        return $stmt->execute();
    }

    public function eliminarCaja(int $cajaid): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM cajas WHERE id = ?");
        $stmt->bind_param("i", $cajaid);
        return $stmt->execute();
    }
}
?>