<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once "conn.php";
require_once "funciones_navbar.php";

$controlador = new FuncionesNavbar($conn);
$controlador->renderizarPanel(1);
?>