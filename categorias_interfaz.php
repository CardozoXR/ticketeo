<?php
include "categorias_sp.php";
include "conn.php";

$categorias = new Categorias();

$getCategorias = $categorias->obtenerTodasLasCategorias();
$categoriaPorID = $categorias->obtenerCategoriaEnOrden();

function idCategoria($nombre)
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

function catInicial($nombre)
{
    $nombre = idCategoria($nombre);

    if($nombre == "medidas"){
        $class = "cat-selected";
    };

    return $class;
}

function getCategorias($recargar = false)
{
    static $categorias = null;

    if ($categorias === null || $recargar) {
        global $conn;
        $sp = new Categorias;
        $categorias = $sp->obtenerCategoriaEnOrden();
    }

    return $categorias;
}



?>