<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REGISTRO - TicketeoUY</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="body1">
        <img src="imgs/logo.png" alt="Logo After Banco" class="logo">

        <div class="card">
            <div class="card-header">
            <h1>INICIAR CAJA 1</h1>
            <p>Configurá los datos para iniciar a operar</p>
      </div>

    <form id="formInicio">
        <div class="form-group">
        <label for="nombre">Nombre y apellido</label>
        <input type="text" id="nombre" placeholder="Ej.: Juan Cardozo" autocomplete="name">
        </div>

        <div class="form-group">
        <label>Crear PIN</label>
        <span class="help-text">Código de 4 dígitos</span>
        <div class="pin-container">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
        </div>
        </div>

        <button type="button" class="btn-submit">REGISTRAR E INICIAR</button>
    
        <div class="card-footer">
            developed by <strong>The Background Group</strong>
        </div>

    </form>
        
    </div>
<script src="scriptRegister.js"></script>
</body>
</html>