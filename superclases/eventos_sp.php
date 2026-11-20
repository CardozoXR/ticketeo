<?php
require_once __DIR__ . "/../conn/conn.php";

class Eventos
{
    private mysqli $conn;

    public function __construct(?mysqli $conexion = null)
    {
        $this->conn = $conexion ?? obtenerConexion();
    }

    private function obtenerColumnaEstado(): ?string
    {
        static $columna = false;
        if ($columna !== false) {
            return $columna;
        }

        $res = $this->conn->query("SHOW COLUMNS FROM eventos");
        $campos = array_column($res->fetch_all(MYSQLI_ASSOC), 'Field');

        if (in_array('activo', $campos, true)) {
            $columna = 'activo';
        } elseif (in_array('estado', $campos, true)) {
            $columna = 'estado';
        } else {
            $columna = null;
        }

        return $columna;
    }

    public function obtenerEventos(): array
    {
        $resultado = $this->conn->query("SELECT * FROM eventos ORDER BY fecha DESC, id DESC");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEventosPorFecha(string $fecha): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM eventos WHERE fecha = ?");
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEventosActivos(): array
    {
        $col = $this->obtenerColumnaEstado();

        if ($col === 'activo') {
            $resultado = $this->conn->query("SELECT * FROM eventos WHERE activo = 1 ORDER BY id DESC");
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }

        if ($col === 'estado') {
            $resultado = $this->conn->query("SELECT * FROM eventos WHERE estado IN (1, '1', 'activo') ORDER BY id DESC");
            return $resultado->fetch_all(MYSQLI_ASSOC);
        }

        $resultado = $this->conn->query("SELECT * FROM eventos ORDER BY id DESC");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEventoActivo(): ?array
    {
        $col = $this->obtenerColumnaEstado();
        $evento = null;

        if ($col === 'activo') {
            $res = $this->conn->query("SELECT * FROM eventos WHERE activo = 1 ORDER BY id DESC LIMIT 1");
            $evento = $res->fetch_assoc() ?: null;
        } elseif ($col === 'estado') {
            $res = $this->conn->query("SELECT * FROM eventos WHERE estado IN (1, '1', 'activo') ORDER BY id DESC LIMIT 1");
            $evento = $res->fetch_assoc() ?: null;
        }

        if (!$evento) {
            $res = $this->conn->query("SELECT * FROM eventos ORDER BY id DESC LIMIT 1");
            $evento = $res->fetch_assoc() ?: null;
        }

        return $evento;
    }

    public function obtenerEventoPorID(int $eventoid): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM eventos WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $eventoid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function agregarEvento(string $nombre, string $fecha, string $logo, $estado): bool
    {
        $col = $this->obtenerColumnaEstado();

        if ($col !== null) {
            $stmt = $this->conn->prepare("INSERT INTO eventos (nombre, fecha, logo, {$col}) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $fecha, $logo, $estado);
            return $stmt->execute();
        }

        $stmt = $this->conn->prepare("INSERT INTO eventos (nombre, fecha, logo) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $fecha, $logo);
        return $stmt->execute();
    }

    public function editarEvento(int $eventoid, string $nombre, string $fecha, string $logo, $estado): bool
    {
        $col = $this->obtenerColumnaEstado();

        if ($col !== null) {
            $stmt = $this->conn->prepare("UPDATE eventos SET nombre = ?, fecha = ?, logo = ?, {$col} = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $nombre, $fecha, $logo, $estado, $eventoid);
            return $stmt->execute();
        }

        $stmt = $this->conn->prepare("UPDATE eventos SET nombre = ?, fecha = ?, logo = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre, $fecha, $logo, $eventoid);
        return $stmt->execute();
    }

    public function eliminarEvento(int $eventoid): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM eventos WHERE id = ?");
        $stmt->bind_param("i", $eventoid);
        return $stmt->execute();
    }
}
?>