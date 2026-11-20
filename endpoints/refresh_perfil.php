<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['caja'])) {
    http_response_code(401);
    exit;
}

require_once __DIR__ . "/../interfaces/navbar_interfaz.php";

$navbar = new FuncionesNavbar();
$navbar->renderizarPerfil((int)$_SESSION['caja'], $_SESSION['evento_id'] ?? null);
?>