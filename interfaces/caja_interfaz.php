<?php
require_once __DIR__ . "/../superclases/caja_sp.php";

function getCajas(bool $recargar = false): array
{
    static $cajas = null;
    if ($cajas === null || $recargar) {
        $cajas = (new Cajas())->obtenerTodasLasCajas();
    }
    return $cajas;
}

function obtenerCajaPorId(int $cajaId): ?array
{
    return (new Cajas())->obtenerCajaPorID($cajaId);
}

function obtenerCajaPorNumero(int $numeroCaja, int $eventoId): ?array
{
    return (new Cajas())->obtenerCajaPorNumero($numeroCaja, $eventoId);
}

function obtenerContextoCaja(int $numeroCaja, int $eventoId): ?array
{
    return (new Cajas())->obtenerContextoCaja($numeroCaja, $eventoId);
}

function obtenerCajasPorEvento(int $eventoId): array
{
    return (new Cajas())->obtenerCajasPorEvento($eventoId);
}

function obtenerCajasActivas(): array
{
    return (new Cajas())->obtenerCajasActivas();
}

function obtenerCajasActivasPorEvento(int $eventoId, int $activo = 1): array
{
    return (new Cajas())->obtenerCajasActivasPorEvento($eventoId, $activo);
}

function crearCaja(int $eventoId, int $numero, string $nombre, string $impresora, int $activo): bool
{
    return (new Cajas())->agregarCaja($eventoId, $numero, $nombre, $impresora, $activo);
}

function editarCaja(int $cajaId, int $eventoId, int $numero, string $nombre, string $impresora, int $activo): bool
{
    return (new Cajas())->editarCaja($cajaId, $eventoId, $numero, $nombre, $impresora, $activo);
}

function eliminarCaja(int $cajaId): bool
{
    return (new Cajas())->eliminarCaja($cajaId);
}
?>