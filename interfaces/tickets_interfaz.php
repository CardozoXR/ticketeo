<?php
include_once __DIR__ . "/../conn/conn.php";
include_once __DIR__ . "/../superclases/tickets_sp.php";

function obtenerTodosLosTickets()
{
    $sp = new Tickets();
    return $sp->obtenerTodosLosTickets();
}

function obtenerTicketsPorVenta($venta_id)
{
    $sp = new Tickets();
    return $sp->obtenerTicketsPorVenta($venta_id);
}