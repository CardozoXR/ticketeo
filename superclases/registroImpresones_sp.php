<?php
class Impresiones
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

    public function obtenerImpresionesPorCajero($cajeroid)
    {
        $sql = "SELECT * FROM registro_impresiones WHERE cajero_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cajeroid);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerUltimas10ImpresionesPorCajero($cajeroid)
    {
        $sql = "SELECT * FROM registro_impresiones WHERE cajero_id = ? ORDER BY creado DESC LIMIT 10";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cajeroid);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerReimpresionesPorCajero($cajeroid)
    {
        $sql = "SELECT * FROM registro_impresiones WHERE cajero_id = ? AND tipo = 'reimpresion'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $cajeroid);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function agregarImpresion($venta_id, $ticket_id, $cajero_id, $exitosa)
    {
        $tipo = 'original';
        
        $sql = "INSERT INTO registro_impresiones (venta_id, ticket_id, cajero_id, tipo, exitosa) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $venta_id, $ticket_id, $cajero_id, $tipo, $exitosa);
        return $stmt->execute();
    }

    public function agregarReimpresion($venta_id, $ticket_id, $cajero_id, $exitosa)
    {
        $tipo = 'reimpresion';
        
        $sql = "INSERT INTO registro_impresiones (venta_id, ticket_id, cajero_id, tipo, exitosa) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $venta_id, $ticket_id, $cajero_id, $tipo, $exitosa);
        return $stmt->execute();
    }
}