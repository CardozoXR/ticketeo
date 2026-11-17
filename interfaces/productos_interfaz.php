<?php
include_once __DIR__ . "/../conn/conn.php";
include_once __DIR__ . "/../superclases/productos_sp.php";
include_once __DIR__ . "/categorias_interfaz.php";

function getProductos($eventoId, $categoriaId)
{
    static $cache = [];
    $key = $eventoId . '-' . $categoriaId;

    if (!isset($cache[$key])) {
        $sp = new Productos();
        $cache[$key] = $sp->obtenerProductosPorCategoria($eventoId, $categoriaId);
    }

    return $cache[$key];
}

function resetNombreCategoria($nombre)
{
    return idCategoria($nombre);
}

function classCat($nombre)
{
    $nombre = resetNombreCategoria($nombre);

    if ($nombre === "medidas") {
        return 'style="display: grid;"';
    }

    return 'style="display: none;"';
}
?>