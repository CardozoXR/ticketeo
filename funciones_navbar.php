<?php
require_once "navbar_sp.php";

class FuncionesNavbar {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new NavbarSP($conexion);
    }

    public function renderizarPanel($evento_id = 1) {
        $caja1 = $this->modelo->getDatosCaja(1, $evento_id);
        $caja2 = $this->modelo->getDatosCaja(2, $evento_id);
        $resumen = $this->modelo->getDatosResumen($evento_id);

        require "vista_navbar.php";
    }

    public function renderizarPerfil($caja_numero, $evento_id = 1) {
        $perfilCaja = $this->modelo->getDatosCaja($caja_numero, $evento_id);
        require "vista_perfil.php";
    }

    public function actualizarNombre($cajero_id, $nombre) {
        $nombre = trim($nombre);
        if ($cajero_id <= 0 || $nombre === '') {
            return ["status" => "error", "message" => "El nombre no puede estar vacío."];
        }

        if ($this->modelo->cambiarNombreCajero($cajero_id, $nombre)) {
            $cajaCajero = $this->modelo->getCajaNumeroPorCajero($cajero_id);
            if (isset($_SESSION['caja']) && (int)$_SESSION['caja'] === $cajaCajero) {
                $_SESSION['nombre'] = $nombre;
            }
            return [
                "status" => "success",
                "caja_num" => $cajaCajero,
                "nuevo_nombre" => $nombre
            ];
        }

        return ["status" => "error", "message" => "Error al actualizar en la base de datos."];
    }

    public function actualizarPin($cajero_id, $pin) {
        $pin = trim($pin);
        if ($cajero_id <= 0 || !preg_match('/^\d{4}$/', $pin)) {
            return ["status" => "error", "message" => "El PIN debe tener exactamente 4 dígitos."];
        }

        if ($this->modelo->resetPinCajero($cajero_id, $pin)) {
            return ["status" => "success"];
        }

        return ["status" => "error", "message" => "Error al actualizar el PIN."];
    }
}
?>