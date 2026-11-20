<?php
session_start();
$caja = filter_input(INPUT_GET, 'caja', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
    ?: (int)($_SESSION['caja'] ?? 1);

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

header("Location: login.php?caja={$caja}");
exit;
?>