<?php
include "productos_sp.php";

$productos = new Productos();

$productosList = $productos->obtenerTodosLosProductos();
$obtenerProductos = $productos->obtenerProductosEnOrden();

function getProductos($eventoId, $categoriaId)
{
    static $cache = [];
    $key = $eventoId . '-' . $categoriaId;

    if (!isset($cache[$key])) {
        global $conn;
        $sp = new Productos($conn);   // <-- nombre real de tu clase
        $cache[$key] = $sp->obtenerProductosPorCategoria($eventoId, $categoriaId);
    }

    return $cache[$key];
}
?>