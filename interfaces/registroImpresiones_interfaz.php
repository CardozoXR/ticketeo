<?php
require_once __DIR__ . "/../superclases/registroImpresiones_sp.php";

function obtenerImpresionesPorCajero(int $cajeroid): array
{
    return (new Impresiones())->obtenerImpresionesPorCajero($cajeroid);
}

function obtenerUltimas10ImpresionesPorCajero(int $cajeroid): array
{
    return (new Impresiones())->obtenerUltimas10ImpresionesPorCajero($cajeroid);
}

function obtenerRegistroImpresionesPorCajero(int $cajeroid): array
{
    return (new Impresiones())->obtenerReimpresionesPorCajero($cajeroid);
}

function agregarImpresion(int $venta_id, int $ticket_id, int $cajero_id, int $exitosa = 1): bool
{
    return (new Impresiones())->agregarImpresion($venta_id, $ticket_id, $cajero_id, $exitosa);
}

function agregarReimpresion(int $venta_id, int $ticket_id, int $cajero_id, int $exitosa = 1): bool
{
    return (new Impresiones())->agregarReimpresion($venta_id, $ticket_id, $cajero_id, $exitosa);
}
?>