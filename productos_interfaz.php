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
        $sp = new Productos;
        $cache[$key] = $sp->obtenerProductosPorCategoria($eventoId, $categoriaId);
    }

    return $cache[$key];
}

function classCat($nombre)
{
    $nombre = resetNombreCategoria($nombre);

    if($nombre == "medidas"){
        $style = 'style="display: grid";';
    } else{
        $style = 'style="display: none";';
    };

    return $style;
}

function resetNombreCategoria($nombre)
{
    $nombre = mb_strtolower($nombre, 'UTF-8');

    // Quitar tildes y diéresis
    $nombre = strtr($nombre, [
        'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
        'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
        'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
    ]);

    // Espacios y caracteres raros -> guion
    $nombre = preg_replace('/[^a-z0-9]+/', '-', $nombre);

    return trim($nombre, '-');
}
?>