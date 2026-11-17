<?php
class Productos
{
    private $conn;
    private $dbhost = "localhost";
    private $dbuser = "root";
    private $dbpass = "root";
    private $dbname = "ticketeo";

    function __construct()
    {
        $this->connect_db();
    }
    public function connect_db()
    {
        $this->conn = mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
        if (mysqli_connect_error()) {
            die("Conexión a la base de datos falló " . mysqli_connect_error() . mysqli_connect_errno());
        }
    }

    public function obtenerTodosLosProductos()
    {
        $sql = "SELECT * FROM productos";
        $resultado = $this->conn->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductosEnOrden()
    {
        $sql = "SELECT * FROM productos ORDER BY id ASC, nombre ASC";
        $resultado = $this->conn->query($sql);

        if (!$resultado) {
            echo "Error en la consulta SQL: " . $this->conn->error;
            return [];
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductosPorCategoria($eventoId, $categoriaId)
    {
        $sql = "SELECT pe.id, pe.precio, pe.etiqueta, p.nombre, p.img
                FROM productos_evento pe
                INNER JOIN productos p ON p.id = pe.producto_id
                WHERE pe.evento_id = ?
                AND p.categoria_id = ?
                AND pe.disponible = 1
                AND p.activo = 1
                ORDER BY pe.orden ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventoId, $categoriaId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductoPorID($productoId)
    {
        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerProductosPorEvento($eventoId)
    {
        $sql = "SELECT pe.id, pe.precio, pe.etiqueta, p.nombre, p.img
                FROM productos_evento pe
                INNER JOIN productos p ON p.id = pe.producto_id
                WHERE pe.evento_id = ?
                AND pe.disponible = 1
                AND p.activo = 1
                ORDER BY pe.orden ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $eventoId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductosActivos()
    {
        $sql = "SELECT * FROM productos WHERE activo = 1";
        $resultado = $this->conn->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarProducto($nombre, $categoriaId, $img, $activo)
    {
        $sql = "INSERT INTO productos (nombre, categoria_id, img, activo) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siss", $nombre, $categoriaId, $img, $activo);
        return $stmt->execute();
    }

    public function editarProducto($productoId, $nombre, $categoriaId, $img, $activo)
    {
        $sql = "UPDATE productos SET nombre = ?, categoria_id = ?, img = ?, activo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sissi", $nombre, $categoriaId, $img, $activo, $productoId);
        return $stmt->execute();
    }

    public function eliminarProducto($productoId)
    {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $productoId);
        return $stmt->execute();
    }
}
?>