<?php
class Cajeros
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

    public function obtenerTodosLosCajeros()
    {
        $sql = "SELECT * FROM cajeros";
        $resultado = $this->conn->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCajeroPorCaja($cajaid)
    {
        $sql = "SELECT * FROM cajeros WHERE caja_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cajaid);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarCajero($cajaid, $nombre, $pin, $activo)
    {
        $sql = "INSERT INTO cajeros (caja_id, nombre, pin, activo) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $cajaid, $nombre, $pin, $activo);
        return $stmt->execute();
    }

    public function editarCajero($cajaid, $nombre, $pin, $activo)
    {
        $sql = "UPDATE cajeros SET nombre = ?, pin = ?, activo = ? WHERE caja_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $pin, $activo, $cajaid);
        return $stmt->execute();
    }

    public function eliminarCajero($cajaid)
    {
        $sql = "DELETE FROM cajeros WHERE caja_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cajaid);
        return $stmt->execute();
    }
}