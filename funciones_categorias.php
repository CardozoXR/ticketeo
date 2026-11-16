<?php
include_once "conn.php";
include_once "categorias_sp.php";

function idCategoria($nombre)
{
    $nombre = mb_strtolower($nombre, 'UTF-8');

    $nombre = strtr($nombre, [
        'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a',
        'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
        'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
    ]);

    $nombre = preg_replace('/[^a-z0-9]+/', '-', $nombre);

    return trim($nombre, '-');
}

function catInicial($nombre)
{
    return ($nombre === 'Medidas') ? 'cat-selected' : '';
}

function getCategorias($recargar = false)
{
    static $categorias = null;

    if ($categorias === null || $recargar) {
        $sp = new Categorias();
        $categorias = $sp->obtenerCategoriaEnOrden();
    }

    return $categorias;
}

$categoriaPorID = getCategorias();
?>