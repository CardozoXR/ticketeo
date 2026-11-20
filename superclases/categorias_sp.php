<?php
require_once __DIR__ . "/../conn/conn.php";

class Categorias
{
    private mysqli $conn;

    public function __construct(?mysqli $conexion = null)
    {
        $this->conn = $conexion ?? obtenerConexion();
    }

    public function obtenerTodasLasCategorias(): array
    {
        $resultado = $this->conn->query("SELECT * FROM categorias");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCategoriaPorID(int $idcategoria): ?array
    {
        if ($idcategoria <= 0) {
            return null;
        }
        $stmt = $this->conn->prepare("SELECT * FROM categorias WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $idcategoria);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function obtenerCategoriaEnOrden(): array
    {
        $resultado = $this->conn->query("SELECT * FROM categorias ORDER BY orden ASC, nombre ASC");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarCategoria(string $nombre, int $orden): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO categorias (nombre, orden) VALUES (?, ?)");
        $stmt->bind_param("si", $nombre, $orden);
        return $stmt->execute();
    }

    public function editarCategoria(int $idcategoria, string $nombre, int $orden): bool
    {
        $stmt = $this->conn->prepare("UPDATE categorias SET nombre = ?, orden = ? WHERE id = ?");
        $stmt->bind_param("sii", $nombre, $orden, $idcategoria);
        return $stmt->execute();
    }

    public function eliminarCategoria(int $idcategoria): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM categorias WHERE id = ?");
        $stmt->bind_param("i", $idcategoria);
        return $stmt->execute();
    }
}
?>