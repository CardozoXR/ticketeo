<?php
class Eventos
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

    public function obtenerEventos()
    {
        $sql = "SELECT * FROM eventos";
        $resultado = $this->conn->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEventosPorFecha($fecha)
    {
        $sql = "SELECT * FROM eventos WHERE fecha = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $fecha);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEventosActivos()
    {
        $sql = "SELECT * FROM eventos WHERE activo = 1";
        $resultado = $this->conn->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerEventoPorID($eventoid)
    {
        $sql = "SELECT * FROM eventos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $eventoid);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    public function agregarEvento($nombre, $fecha, $logo, $estado)
    {
        $sql = "INSERT INTO eventos (nombre, fecha, logo, activo) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $fecha, $logo, $estado);
        return $stmt->execute();
    }

    public function editarEvento($eventoid, $nombre, $fecha, $logo, $estado)
    {
        $sql = "UPDATE eventos SET nombre = ?, fecha = ?, logo = ?, activo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $nombre, $fecha, $logo, $estado, $eventoid);
        return $stmt->execute();
    }

    public function eliminarEvento($eventoid)
    {
        $sql = "DELETE FROM eventos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $eventoid);
        return $stmt->execute();
    }
}