<?php
require_once __DIR__ . "/../superclases/cajeros_sp.php";
require_once __DIR__ . "/eventos_interfaz.php";
require_once __DIR__ . "/caja_interfaz.php";

function getCajeros(bool $recargar = false): array
{
    static $cajeros = null;
    if ($cajeros === null || $recargar) {
        $cajeros = (new Cajeros())->obtenerTodosLosCajeros();
    }
    return $cajeros;
}

function obtenerCajeroPorCaja(int $caja): ?array
{
    $resultado = (new Cajeros())->obtenerCajeroPorCaja($caja);
    return empty($resultado) ? null : $resultado;
}

function obtenerCajaDeCajero($cajero_id){
    return (new Cajeros())->obtenerCajaDeCajero($cajero_id);
}

function obtenerCajeroActivoPorNumeroCaja(int $numeroCaja, int $eventoId): ?array
{
    return (new Cajeros())->obtenerCajeroPorNumeroCaja($numeroCaja, $eventoId);
}

function crearCajero(int $cajaid, string $nombre, string $pinHash): int
{
    return (new Cajeros())->agregarCajero($cajaid, $nombre, $pinHash, 1);
}

function obtenerDatosPantallaLogin(int $numeroCaja): array
{
    $evento = obtenerEventoActivo();
    if (!$evento) {
        return ['error' => 'No hay ningún evento activo configurado.', 'cajero' => null, 'modoLogin' => false];
    }

    $cajero = obtenerCajeroActivoPorNumeroCaja($numeroCaja, (int)$evento['id']);

    return [
        'error'     => null,
        'cajero'    => $cajero,
        'modoLogin' => ($cajero !== null)
    ];
}

function procesarAccesoCaja(int $numeroCaja, string $pin, string $nombreNuevo = ''): array
{
    $intentosKey = "intentos_login_caja_{$numeroCaja}";
    $bloqueoKey  = "bloqueo_login_caja_{$numeroCaja}";

    if (isset($_SESSION[$bloqueoKey]) && time() < $_SESSION[$bloqueoKey]) {
        $segundos = $_SESSION[$bloqueoKey] - time();
        return ['ok' => false, 'error' => "Demasiados intentos. Esperá {$segundos}s."];
    }

    if (!preg_match('/^\d{4}$/', $pin)) {
        return ['ok' => false, 'error' => 'El PIN debe tener 4 dígitos.'];
    }

    $evento = obtenerEventoActivo();
    if (!$evento) {
        return ['ok' => false, 'error' => 'No hay evento activo.'];
    }

    $eventoId = (int)$evento['id'];
    $cajaInfo = obtenerCajaPorNumero($numeroCaja, $eventoId);
    if (!$cajaInfo) {
        return ['ok' => false, 'error' => "La Caja {$numeroCaja} no está habilitada para este evento."];
    }

    $cajero = obtenerCajeroActivoPorNumeroCaja($numeroCaja, $eventoId);

    if ($cajero !== null) {
        $pinGuardado = (string)$cajero['pin'];
        $valido = password_verify($pin, $pinGuardado) || hash_equals($pinGuardado, $pin);

        if (!$valido) {
            $_SESSION[$intentosKey] = ($_SESSION[$intentosKey] ?? 0) + 1;
            if ($_SESSION[$intentosKey] >= 5) {
                $_SESSION[$bloqueoKey]  = time() + 60;
                $_SESSION[$intentosKey] = 0;
                return ['ok' => false, 'error' => 'PIN incorrecto. Caja bloqueada por 60 segundos.'];
            }
            return ['ok' => false, 'error' => 'PIN incorrecto.'];
        }

        if (!str_starts_with($pinGuardado, '$2y$') && !str_starts_with($pinGuardado, '$argon2')) {
            (new Cajeros())->actualizarPinHash((int)$cajero['id'], password_hash($pin, PASSWORD_DEFAULT));
        }

        unset($_SESSION[$intentosKey], $_SESSION[$bloqueoKey]);
        iniciarSesionCajero($cajero['nombre'], $numeroCaja, (int)$cajero['id'], (int)$cajaInfo['id'], $eventoId);
        return ['ok' => true];
    }

    $nombreLimpio = trim($nombreNuevo);
    if ($nombreLimpio === '' || mb_strlen($nombreLimpio) > 60) {
        return ['ok' => false, 'error' => 'Ingresá tu nombre y apellido.'];
    }

    try {
        $pinHash  = password_hash($pin, PASSWORD_DEFAULT);
        $cajeroId = crearCajero((int)$cajaInfo['id'], $nombreLimpio, $pinHash);
        unset($_SESSION[$intentosKey], $_SESSION[$bloqueoKey]);
        iniciarSesionCajero($nombreLimpio, $numeroCaja, $cajeroId, (int)$cajaInfo['id'], $eventoId);
        return ['ok' => true];
    } catch (Throwable $e) {
        error_log("Error creando cajero: " . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo registrar la caja. Recargá la página.'];
    }
}

function iniciarSesionCajero(string $nombre, int $numeroCaja, int $cajeroId, int $cajaId, int $eventoId): void
{
    session_regenerate_id(true);
    $_SESSION['nombre']    = $nombre;
    $_SESSION['caja']      = $numeroCaja;
    $_SESSION['caja_id']   = $cajaId;
    $_SESSION['cajero_id'] = $cajeroId;
    $_SESSION['evento_id'] = $eventoId;
}
?>