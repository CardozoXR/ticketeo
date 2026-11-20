<?php
session_start();
require_once __DIR__ . '/interfaces/cajeros_interfaz.php';

$caja = filter_input(INPUT_GET, 'caja', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$caja) {
    http_response_code(400);
    exit('Caja inválida');
}

$estado    = obtenerDatosPantallaLogin($caja);
$error     = $estado['error'];
$cajero    = $estado['cajero'];
$modoLogin = $estado['modoLogin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $pin    = $_POST['pin'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $res    = procesarAccesoCaja($caja, $pin, $nombre);

    if ($res['ok']) {
        header("Location: caja{$caja}.php");
        exit;
    }
    $error = $res['error'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modoLogin ? 'INICIAR SESIÓN' : 'REGISTRO' ?> - Ticketeo</title>
    <link rel="stylesheet" href="estilos/estilos.css">
</head>
<body>
  <div class="body1">
    <img src="imgs/logo.png" alt="Logo After Banco" class="logo">

    <div class="card">
      <div class="card-header">
        <?php if ($modoLogin): ?>
          <h1>BIENVENIDO</h1>
          <p class="user-name"><?= htmlspecialchars($cajero['nombre']) ?></p>
          <p>Ingresá tu PIN para abrir la caja</p>
        <?php else: ?>
          <h1>INICIAR CAJA <?= (int)$caja ?></h1>
          <p>Configurá los datos para iniciar a operar</p>
        <?php endif; ?>
      </div>

      <?php if ($error): ?>
        <p style="color: #ff4d4d; text-align: center; margin-bottom: 10px; font-weight: bold;">
          <?= htmlspecialchars($error) ?>
        </p>
      <?php endif; ?>

      <form id="formCaja" method="post">
        <?php if (!$modoLogin): ?>
          <div class="form-group">
            <label for="nombre">Nombre y apellido</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ej.: Juan Cardozo"
                   autocomplete="name" required>
          </div>
        <?php endif; ?>

        <div class="form-group">
          <label><?= $modoLogin ? 'Ingresar PIN' : 'Crear PIN' ?></label>
          <span class="help-text">Código de 4 dígitos</span>
          <div class="pin-container">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
          </div>
          <input type="hidden" name="pin" id="pin">
        </div>

        <button type="submit" class="btn-submit">
          <?= $modoLogin ? 'INICIAR CAJA' : 'REGISTRAR E INICIAR' ?>
        </button>
      </form>

      <div class="card-footer">
        developed by <strong>The Background Group</strong>
      </div>
    </div>
  </div>
<script src="scripts/scriptLogin.js"></script>
</body>
</html>