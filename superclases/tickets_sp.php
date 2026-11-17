<?php
class Tickets
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

    public function obtenerTodosLosTickets()
    {
        $sql = "SELECT * FROM tickets";
        $resultado = $this->conn->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerTicketsPorVenta($venta_id)
    {
        $sql = "SELECT * FROM tickets WHERE venta_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $venta_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
?>