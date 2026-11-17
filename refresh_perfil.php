<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once __DIR__ . "/conn/conn.php";
require_once "funciones_navbar.php";

$cajaActual = (int)($_SESSION['caja'] ?? 1);
$controlador = new FuncionesNavbar($conn);
$controlador->renderizarPerfil($cajaActual, 1);
?>