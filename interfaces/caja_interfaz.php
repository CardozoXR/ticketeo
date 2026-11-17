<?php
include_once __DIR__ . "/../conn/conn.php";
include_once __DIR__ . "/../superclases/caja_sp.php";

function getCajas($recargar = false)
{
    static $cajas = null;

    if ($cajas === null || $recargar) {
        $sp = new Cajas();
        $cajas = $sp->obtenerTodasLasCajas();
    }

    return $cajas;
}

function obtenerCajaPorId(int $cajaId)
{
    $sp = new Cajas();
    $resultado = $sp->obtenerCajaPorId($cajaId);

    return empty($resultado) ? null : $resultado;
}

function obtenerCajasPorEvento(int $eventoId)
{
    $sp = new Cajas();
    return $sp->obtenerCajasPorEvento($eventoId);
}

function obtenerCajasActivas()
{
    $sp = new Cajas();
    return $sp->obtenerCajasActivas();
}

function crearCaja(int $eventoId, string $numero, string $nombre, string $impresora, int $activo)
{
    $sp = new Cajas();
    return $sp->agregarCaja($eventoId, $numero, $nombre, $impresora, $activo);
}

function editarCaja(int $cajaId, int $eventoId, string $numero, string $nombre, string $impresora, int $activo)
{
    $sp = new Cajas();
    return $sp->editarCaja($cajaId, $eventoId, $numero, $nombre, $impresora, $activo);
}

function eliminarCaja(int $cajaId)
{
    $sp = new Cajas();
    return $sp->eliminarCaja($cajaId);
}