<?php
require_once __DIR__ . "/../conn/conn.php";

class Productos
{
    private mysqli $conn;

    public function __construct(?mysqli $conexion = null)
    {
        $this->conn = $conexion ?? obtenerConexion();
    }

    public function obtenerTodosLosProductos(): array
    {
        $resultado = $this->conn->query("SELECT * FROM productos");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductosEnOrden(): array
    {
        $resultado = $this->conn->query("SELECT * FROM productos ORDER BY id ASC, nombre ASC");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductosPorCategoria(int $eventoId, int $categoriaId): array
    {
        $stmt = $this->conn->prepare("SELECT pe.id, pe.producto_id, pe.precio, pe.etiqueta, p.nombre, p.img
                                      FROM productos_evento pe
                                      INNER JOIN productos p ON p.id = pe.producto_id
                                      WHERE pe.evento_id = ?
                                        AND p.categoria_id = ?
                                        AND pe.disponible = 1
                                        AND p.activo = 1
                                      ORDER BY pe.orden ASC");
        $stmt->bind_param("ii", $eventoId, $categoriaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductoEvento(int $productoEventoId, int $eventoId): ?array
    {
        $stmt = $this->conn->prepare("SELECT pe.id, pe.producto_id, pe.precio, pe.etiqueta, p.nombre
                                      FROM productos_evento pe
                                      INNER JOIN productos p ON p.id = pe.producto_id
                                      WHERE pe.id = ?
                                        AND pe.evento_id = ?
                                        AND pe.disponible = 1
                                        AND p.activo = 1
                                      LIMIT 1");
        $stmt->bind_param("ii", $productoEventoId, $eventoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function obtenerOCrearProductoConsumicion(int $eventoId): array
    {
        $stmt = $this->conn->prepare("SELECT pe.id, pe.producto_id, pe.precio, pe.etiqueta, p.nombre
                                      FROM productos_evento pe
                                      INNER JOIN productos p ON pe.producto_id = p.id
                                      WHERE pe.evento_id = ? AND (pe.id = 26 OR p.nombre = 'Consumición')
                                      LIMIT 1");
        $stmt->bind_param("i", $eventoId);
        $stmt->execute();
        $existente = $stmt->get_result()->fetch_assoc();
        if ($existente) {
            return $existente;
        }

        $stmtProd = $this->conn->prepare("SELECT id FROM productos WHERE nombre = 'Consumición' LIMIT 1");
        $stmtProd->execute();
        $prod = $stmtProd->get_result()->fetch_assoc();

        if ($prod) {
            $productoId = (int)$prod['id'];
        } else {
            $stmtInsProd = $this->conn->prepare("INSERT INTO productos (categoria_id, nombre, img, activo)
                                                 VALUES (4, 'Consumición', 'imgs/consumicion.png', 1)");
            $stmtInsProd->execute();
            $productoId = (int)$this->conn->insert_id;
        }

        $stmtInsPe = $this->conn->prepare("INSERT INTO productos_evento (evento_id, producto_id, precio, etiqueta, orden, disponible)
                                           VALUES (?, ?, 0.00, 'CONSUMO', 1, 1)");
        $stmtInsPe->bind_param("ii", $eventoId, $productoId);
        $stmtInsPe->execute();

        return [
            'id'          => (int)$this->conn->insert_id,
            'producto_id' => $productoId,
            'precio'      => 0.00,
            'etiqueta'    => 'CONSUMO',
            'nombre'      => 'Consumición'
        ];
    }

    public function obtenerProductoPorID(int $productoId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $productoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function obtenerProductosPorEvento(int $eventoId): array
    {
        $stmt = $this->conn->prepare("SELECT pe.id, pe.producto_id, pe.precio, pe.etiqueta, p.nombre, p.img
                                      FROM productos_evento pe
                                      INNER JOIN productos p ON p.id = pe.producto_id
                                      WHERE pe.evento_id = ?
                                        AND pe.disponible = 1
                                        AND p.activo = 1
                                      ORDER BY pe.orden ASC");
        $stmt->bind_param("i", $eventoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductosActivos(): array
    {
        $resultado = $this->conn->query("SELECT * FROM productos WHERE activo = 1");
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarProducto(string $nombre, int $categoriaId, string $img, int $activo): bool
    {
        $stmt = $this->conn->prepare("INSERT INTO productos (nombre, categoria_id, img, activo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sisi", $nombre, $categoriaId, $img, $activo);
        return $stmt->execute();
    }

    public function editarProducto(int $productoId, string $nombre, int $categoriaId, string $img, int $activo): bool
    {
        $stmt = $this->conn->prepare("UPDATE productos SET nombre = ?, categoria_id = ?, img = ?, activo = ? WHERE id = ?");
        $stmt->bind_param("sisii", $nombre, $categoriaId, $img, $activo, $productoId);
        return $stmt->execute();
    }

    public function eliminarProducto(int $productoId): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $productoId);
        return $stmt->execute();
    }
}
?>