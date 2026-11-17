<?php
include_once __DIR__ . "/../conn/conn.php";
include_once __DIR__ . "/../superclases/eventos_sp.php";

function obtenerEventos()
{
    $sp = new Eventos();
    return $sp->obtenerEventos();
}

function obtenerEventosPorFecha($fecha)
{
    $sp = new Eventos();
    return $sp->obtenerEventosPorFecha($fecha);
}

function obtenerEventosActivos()
{
    $sp = new Eventos();
    return $sp->obtenerEventosActivos();
}

function obtenerEventoPorID($eventoid)
{
    $sp = new Eventos();
    return $sp->obtenerEventoPorID($eventoid);
}

function agregarEvento($nombre, $fecha, $logo, $estado)
{
    $sp = new Eventos();
    return $sp->agregarEvento($nombre, $fecha, $logo, $estado);
}

function editarEvento($eventoid, $nombre, $fecha, $logo, $estado)
{
    $sp = new Eventos();
    return $sp->editarEvento($eventoid, $nombre, $fecha, $logo, $estado);
}

function eliminarEvento($eventoid)
{
    $sp = new Eventos();
    return $sp->eliminarEvento($eventoid);
}

