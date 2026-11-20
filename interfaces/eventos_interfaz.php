<?php
require_once __DIR__ . "/../superclases/eventos_sp.php";

function obtenerEventos(): array
{
    return (new Eventos())->obtenerEventos();
}

function obtenerEventosPorFecha(string $fecha): array
{
    return (new Eventos())->obtenerEventosPorFecha($fecha);
}

function obtenerEventosActivos(): array
{
    return (new Eventos())->obtenerEventosActivos();
}

function obtenerEventoActivo(): ?array
{
    static $evento = null;
    if ($evento === null) {
        $evento = (new Eventos())->obtenerEventoActivo();
    }
    return $evento;
}

function obtenerEventoPorID(int $eventoid): ?array
{
    return (new Eventos())->obtenerEventoPorID($eventoid);
}

function agregarEvento(string $nombre, string $fecha, string $logo, int $estado): bool
{
    return (new Eventos())->agregarEvento($nombre, $fecha, $logo, $estado);
}

function editarEvento(int $eventoid, string $nombre, string $fecha, string $logo, int $estado): bool
{
    return (new Eventos())->editarEvento($eventoid, $nombre, $fecha, $logo, $estado);
}

function eliminarEvento(int $eventoid): bool
{
    return (new Eventos())->eliminarEvento($eventoid);
}
?>