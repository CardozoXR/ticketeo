<?php
require_once __DIR__ . "/../superclases/tickets_sp.php";

function obtenerTodosLosTickets(): array
{
    return (new Tickets())->obtenerTodosLosTickets();
}

function obtenerTicketsPorVenta(int $venta_id): array
{
    return (new Tickets())->obtenerTicketsPorVenta($venta_id);
}

function obtenerTicketsDetallePorVenta(int $venta_id): array
{
    return (new Tickets())->obtenerTicketsDetallePorVenta($venta_id);
}
?>