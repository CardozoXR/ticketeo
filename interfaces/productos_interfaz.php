<?php
require_once __DIR__ . "/../superclases/productos_sp.php";
require_once __DIR__ . "/categorias_interfaz.php";

function getProductos(int $eventoId, int $categoriaId): array
{
    static $cache = [];
    $key = $eventoId . '-' . $categoriaId;

    if (!isset($cache[$key])) {
        $cache[$key] = (new Productos())->obtenerProductosPorCategoria($eventoId, $categoriaId);
    }

    return $cache[$key];
}

function obtenerProductoEventoParaVenta(int $productoEventoId, int $eventoId): ?array
{
    return (new Productos())->obtenerProductoEvento($productoEventoId, $eventoId);
}

function obtenerProductoConsumicionEvento(int $eventoId): array
{
    return (new Productos())->obtenerOCrearProductoConsumicion($eventoId);
}

function resetNombreCategoria(string $nombre): string
{
    return idCategoria($nombre);
}

function classCat(string $nombre): string
{
    return (resetNombreCategoria($nombre) === "medidas")
        ? 'style="display: grid;"'
        : 'style="display: none;"';
}
?>