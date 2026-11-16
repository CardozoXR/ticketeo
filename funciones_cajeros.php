<?php
include_once "conn.php";
include_once "cajeros_sp.php";

function getCajeros($recargar = false)
{
    static $cajeros = null;

    if ($cajeros === null || $recargar) {
        $sp = new Cajeros();
        $cajeros = $sp->obtenerTodosLosCajeros();
    }

    return $cajeros;
}

function obtenerCajeroPorCaja(int $caja)
{
    $sp = new Cajeros();
    $resultado = $sp->obtenerCajeroPorCaja($caja);

    return empty($resultado) ? null : $resultado;
}

function obtenerInfoCajero(int $caja)
{
    $sp = new Cajeros();
    $resultado = $sp->obtenerInfoCajero($caja);

    return empty($resultado) ? null : $resultado[0];
}

function crearCajero(int $cajaid, string $nombre, string $pin, int $caja)
{
    $sp = new Cajeros();
    return $sp->agregarCajero($cajaid, $nombre, $pin, 1);
}

function iniciarSesionCajero(string $nombre, int $caja): void
{
    session_regenerate_id(true);
    $_SESSION['nombre'] = $nombre;
    $_SESSION['caja']   = $caja;
    header("Location: caja{$caja}.php");
    exit;
}
?>