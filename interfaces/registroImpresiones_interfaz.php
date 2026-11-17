<?php
include_once __DIR__ . "/../conn/conn.php";
include_once __DIR__ . "/../superclases/registroImpresiones_sp.php";

function obtenerImpresionesPorCajero($cajeroid)
{
    $sp = new Impresiones();
    return $sp->obtenerImpresionesPorCajero($cajeroid);
}

function obtenerUltimas10ImpresionesPorCajero($cajeroid)
{
    $sp = new Impresiones();
    return $sp->obtenerUltimas10ImpresionesPorCajero($cajeroid);
}

function obtenerRegistroImpresionesPorCajero($cajeroid)
{
    $sp = new Impresiones();
    return $sp->obtenerReimpresionesPorCajero($cajeroid);
}

function agregarImpresion($venta_id, $ticket_id, $cajero_id, $exitosa)
{
    $sp = new Impresiones();
    return $sp->agregarImpresion($venta_id, $ticket_id, $cajero_id, $exitosa);
}

function agregarReimpresion($venta_id, $ticket_id, $cajero_id, $exitosa)
{
    $sp = new Impresiones();
    return $sp->agregarReimpresion($venta_id, $ticket_id, $cajero_id, $exitosa);
}