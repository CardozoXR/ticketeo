<?php
include_once "cajeros_sp.php";
include_once "conn.php";

function getCajeros($recargar = false)
{
    static $cajeros = null;

    if ($cajeros === null || $recargar) {
        $sp = new Cajeros;
        $cajeros = $sp->obtenerTodosLosCajeros();
    }

    return $cajeros;
}

function obtenerCajeroPorCaja(int $caja)
{
    $sp = new Cajeros;
    $resultado = $sp->obtenerCajeroPorCaja($caja);

    if (empty($resultado)) {
        return null;
    } else {
        return $resultado;
    }
}

function obtenerInfoCajero(int $caja)
{
    $sp = new Cajeros;
    $resultado = $sp->obtenerInfoCajero($caja);

    if (empty($resultado)) {
        return null;
    }

    return $resultado[0];
}

function crearCajero(int $cajaid, string $nombre, string $pin, int $caja)
{
    $sp = new Cajeros;
    $sp->agregarCajero($cajaid, $nombre, $pin, 1);
}