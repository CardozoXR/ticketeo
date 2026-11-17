<?php
class Cajas
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

    public function obtenerTodasLasCajas()
    {
        $sql = "SELECT * FROM cajas";
        $resultado = $this->conn->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCajaPorID($cajaid)
    {
        $sql = "SELECT * FROM cajas WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cajaid);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    public function obtenerCajasPorEvento($eventoid)
    {
        $sql = "SELECT * FROM cajas WHERE evento_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $eventoid);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCajasActivas(){
        $sql = "SELECT * FROM cajas WHERE activo = 1";
        $resultado = $this->conn->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarCaja($eventoid, $numero, $nombre, $impresora, $activo)
    {
        $sql = "INSERT INTO cajas (evento_id, numero, nombre, impresora, activo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $eventoid, $numero, $nombre, $impresora, $activo);
        return $stmt->execute();
    }

    public function editarCaja($cajaid, $eventoid, $numero, $nombre, $impresora, $activo)
    {
        $sql = "UPDATE cajas SET evento_id = ?, numero = ?, nombre = ?, impresora = ?, activo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssss", $eventoid, $numero, $nombre, $impresora, $activo, $cajaid);
        return $stmt->execute();
    }

    public function eliminarCaja($cajaid)
    {
        $sql = "DELETE FROM cajas WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cajaid);
        return $stmt->execute();
    }
}