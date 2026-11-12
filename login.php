<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INICIAR SESIÓN - TicketeoUY</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

  <div class="body1">
    <img src="imgs/logo.png" alt="Logo After Banco" class="logo">

    <div class="card">
      <div class="card-header">
        <h1>BIENVENIDO</h1>
        <p class="user-name">Juan Cardozo</p>
        <p>Ingresá tu PIN para abrir la caja</p>
      </div>

      <form id="formBienvenida">
        <div class="form-group">
          <label>Ingresar PIN</label>
          <span class="help-text">Código de 4 dígitos</span>
          <div class="pin-container">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
          </div>
        </div>

        <button type="button" class="btn-submit">INICIAR CAJA</button>
      </form>

      <div class="card-footer">
        developed by <strong>The Background Group</strong>
      </div>
    </div>
  </div>

  <script src="scriptLogin.js"></script>
</body>
</html>