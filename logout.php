<?php
session_start();
$caja = filter_input(INPUT_GET, 'caja', FILTER_VALIDATE_INT) ?: ($_SESSION['caja'] ?? 1);
session_unset();
session_destroy();
header("Location: login.php?caja=" . (int)$caja);
exit;
?>