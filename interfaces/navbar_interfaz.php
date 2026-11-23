<?php
require_once __DIR__ . "/../superclases/navbar_sp.php";
require_once __DIR__ . "/eventos_interfaz.php";
require_once __DIR__ . "/impresion_interfaz.php";

class FuncionesNavbar
{
    private NavbarSP $modelo;

    public function __construct(?mysqli $conexion = null)
    {
        $this->modelo = new NavbarSP($conexion);
    }

    private function resolverEventoId(?int $eventoId): int
    {
        if ($eventoId !== null && $eventoId > 0) {
            return $eventoId;
        }
        if (!empty($_SESSION['evento_id'])) {
            return (int)$_SESSION['evento_id'];
        }
        $activo = obtenerEventoActivo();
        return $activo ? (int)$activo['id'] : 1;
    }

    public function renderizarPanel(?int $evento_id = null): void
    {
        $eventoReal = $this->resolverEventoId($evento_id);
        $caja1      = $this->modelo->getDatosCaja(1, $eventoReal);
        $caja2      = $this->modelo->getDatosCaja(2, $eventoReal);
        $resumen    = $this->modelo->getDatosResumen($eventoReal);

        require __DIR__ . "/../vistas/vista_navbar.php";
    }

    public function renderizarPerfil(int $caja_numero, ?int $evento_id = null): void
    {
        $eventoReal = $this->resolverEventoId($evento_id);
        $perfilCaja = $this->modelo->getDatosCaja($caja_numero, $eventoReal);

        require __DIR__ . "/../vistas/vista_perfil.php";
    }

    public function actualizarNombre(int $cajero_id, string $nombre): array
    {
        $nombre = trim($nombre);
        if ($cajero_id <= 0 || $nombre === '' || mb_strlen($nombre) > 60) {
            return ["status" => "error", "message" => "El nombre debe tener entre 1 y 60 caracteres."];
        }

        if ($this->modelo->cambiarNombreCajero($cajero_id, $nombre)) {
            $cajaCajero = $this->modelo->getCajaNumeroPorCajero($cajero_id);
            if (isset($_SESSION['caja']) && (int)$_SESSION['caja'] === $cajaCajero) {
                $_SESSION['nombre'] = $nombre;
            }
            return [
                "status"       => "success",
                "caja_num"     => $cajaCajero,
                "nuevo_nombre" => $nombre
            ];
        }

        return ["status" => "error", "message" => "Error al actualizar en la base de datos."];
    }

    public function actualizarPin(int $cajero_id, string $pin): array
    {
        $pin = trim($pin);
        if ($cajero_id <= 0 || !preg_match('/^\d{4}$/', $pin)) {
            return ["status" => "error", "message" => "El PIN debe tener exactamente 4 dígitos."];
        }

        $pinHash = password_hash($pin, PASSWORD_DEFAULT);

        if ($this->modelo->resetPinCajero($cajero_id, $pinHash)) {
            return ["status" => "success"];
        }

        return ["status" => "error", "message" => "Error al actualizar el PIN."];
    }

    public function procesarImprimirResumen(?int $eventoId, int $cajaSesionId, string $cajeroNombre, ?string $nombreImpresora, ?int $cajaFiltroId = null): array
    {
        $eventoReal = $this->resolverEventoId($eventoId);
        $datos      = $this->modelo->obtenerDatosResumenImpresion($eventoReal, $cajaFiltroId);
        $ev         = $datos['evento'];

        $nombreEvento = $ev['nombre'] ?? 'After Banco';
        $fechaEvRaw   = $ev['fecha'] ?? date('Y-m-d');
        $ddMm         = date('d.m', strtotime($fechaEvRaw));
        $fechaEvFmt   = date('d/m/Y', strtotime($fechaEvRaw));

        $infoEvento = array_values(array_filter([
            $ev['ciudad'] ?? 'MONTEVIDEO',
            $ev['lugar'] ?? ($ev['ubicacion'] ?? ''),
            'DÍA ' . $fechaEvFmt,
            !empty($ev['hora_apertura']) ? 'PUERTA APERTURA ' . substr($ev['hora_apertura'], 0, 5) : ''
        ]));

        $resumenPayload = [
            'titulo_head'   => $cajaFiltroId ? "CIERRE DE CAJA {$cajaFiltroId}" : "RESUMEN GENERAL",
            'evento_titulo' => strtoupper($nombreEvento) . " " . $ddMm,
            'evento_nombre' => $nombreEvento,
            'codigo_evento' => 1000 + $eventoReal,
            'info_evento'   => $infoEvento,
            'ventas'        => $datos['ventas'],
            'tickets'       => $datos['tickets'],
            'fecha_emision' => date('d/m/Y H:i:s'),
            'caja_texto'    => $cajaFiltroId ? (string)$cajaFiltroId : "General (Caja {$cajaSesionId})",
            'cajero_nombre' => $cajeroNombre,
        ];

        $impresion = FuncionesImpresion::imprimirResumen($nombreImpresora, $resumenPayload);

        return [
            'status'    => $impresion['ok'] ? 'success' : 'error',
            'message'   => $impresion['error'] ?? 'Resumen procesado.',
            'impresion' => $impresion,
            'resumen'   => $resumenPayload,
        ];
    }
}
?>