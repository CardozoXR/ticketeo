<?php
class Categorias
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

    public function obtenerTodasLasCategorias()
    {
        $sql = "SELECT * FROM categorias";
        $resultado = $this->conn->query($sql);
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerCategoriaPorID($idcategoria)
    {
        if ($idcategoria !== null && is_numeric($idcategoria) && $idcategoria > 0) {
            $sql = "SELECT * FROM categorias WHERE id = $idcategoria";
            $resultado = $this->conn->query($sql);
            if ($resultado) {
                if ($resultado->num_rows > 0) {
                    return $resultado->fetch_assoc();
                } else {
                    return null;
                }
            } else {
                echo "Error en la consulta SQL: " . $this->conn->error;
                return null;
            }
        } else {
            return null;
        }
    }

    public function obtenerCategoriaEnOrden()
    {
        $sql = "SELECT * FROM categorias ORDER BY orden ASC, nombre ASC";
        $resultado = $this->conn->query($sql);

        if (!$resultado) {
            echo "Error en la consulta SQL: " . $this->conn->error;
            return [];
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
?>