<?php
require_once __DIR__ . "/../superclases/categorias_sp.php";

function idCategoria(string $nombre): string
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

function catInicial(string $nombre): string
{
    return ($nombre === 'Medidas') ? 'cat-selected' : '';
}

function getCategorias(bool $recargar = false): array
{
    static $categorias = null;
    if ($categorias === null || $recargar) {
        $categorias = (new Categorias())->obtenerCategoriaEnOrden();
    }
    return $categorias;
}

$categoriaPorID = getCategorias();
?>