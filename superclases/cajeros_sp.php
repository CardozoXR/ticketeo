<?php
require_once __DIR__ . "/../conn/conn.php";

class Cajeros
{
    private mysqli $conn;

    public function __construct(?mysqli $conexion = null)
    {
        $this->conn = $conexion ?? obtenerConexion();
    }

    public function obtenerTodosLosCajeros(): array
    {
        $resultado = $this->conn->query("SELECT * FROM cajeros ORDER BY id ASC");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCajeroPorCaja(int $cajaid): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM cajeros WHERE caja_id = ? AND activo = 1 ORDER BY id DESC");
        $stmt->bind_param("i", $cajaid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCajeroPorNumeroCaja(int $numeroCaja, int $eventoId): ?array
    {
        $stmt = $this->conn->prepare("SELECT cj.*, c.id AS caja_id_real, c.numero AS caja_numero
                                      FROM cajeros cj
                                      JOIN cajas c ON c.id = cj.caja_id
                                      WHERE c.numero = ? AND c.evento_id = ? AND c.activo = 1 AND cj.activo = 1
                                      ORDER BY cj.id DESC
                                      LIMIT 1");
        $stmt->bind_param("ii", $numeroCaja, $eventoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function agregarCajero(int $cajaid, string $nombre, string $pinHash, int $activo = 1): int
    {
        $stmt = $this->conn->prepare("INSERT INTO cajeros (caja_id, nombre, pin, activo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $cajaid, $nombre, $pinHash, $activo);
        $stmt->execute();
        return (int)$this->conn->insert_id;
    }

    public function actualizarPinHash(int $cajeroId, string $pinHash): bool
    {
        $stmt = $this->conn->prepare("UPDATE cajeros SET pin = ? WHERE id = ?");
        $stmt->bind_param("si", $pinHash, $cajeroId);
        return $stmt->execute();
    }

    public function editarCajero(int $cajaid, string $nombre, string $pinHash, int $activo): bool
    {
        $stmt = $this->conn->prepare("UPDATE cajeros SET nombre = ?, pin = ?, activo = ? WHERE caja_id = ?");
        $stmt->bind_param("ssii", $nombre, $pinHash, $activo, $cajaid);
        return $stmt->execute();
    }

    public function eliminarCajero(int $cajaid): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM cajeros WHERE caja_id = ?");
        $stmt->bind_param("i", $cajaid);
        return $stmt->execute();
    }
}
?>