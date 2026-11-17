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
}
?>