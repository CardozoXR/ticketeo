<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function obtenerConexion(): mysqli
{
    static $conn = null;

    if ($conn === null) {$servername = "localhost";
        $username   = "root";
        $password   = "root";
        $dbname     = "ticketeo";

        $conn = new mysqli($servername, $username,$password, $dbname);$conn->set_charset("utf8mb4");
    }

    return $conn;
}

$conn = obtenerConexion();
?>