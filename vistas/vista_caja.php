<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cajaRequerida = $cajaRequerida ?? 1;

if (($_SESSION['caja'] ?? null) !== $cajaRequerida) {
    header("Location: login.php?caja={$cajaRequerida}");
    exit;
}

require_once __DIR__ . "/../interfaces/cajeros_interfaz.php";
require_once __DIR__ . "/../interfaces/categorias_interfaz.php";
require_once __DIR__ . "/../interfaces/productos_interfaz.php";
require_once __DIR__ . "/../interfaces/eventos_interfaz.php";

$evento = obtenerEventoActivo();
if (!$evento) {
    exit("No hay evento activo configurado.");
}

$evento_id = (int)$evento['id'];
$_SESSION['evento_id'] = $evento_id;

$nombre     = $_SESSION['nombre'];
$caja       = (int)$_SESSION['caja'];
$infoEvento = htmlspecialchars($evento['nombre']) . " " . date('d.m', strtotime($evento['fecha']));

if (obtenerCajeroActivoPorNumeroCaja($caja, $evento_id) === null) {
    session_destroy();
    header('Location: login.php?caja=' . $caja);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es-UY">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ticketeo</title>
    <link rel="stylesheet" href="estilos/estilos.css">
    <link rel="stylesheet" href="estilos/estilostickets.css">
    <link rel="stylesheet" href="estilos/estilosticketresumen.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="imgs/ticketeo-icon.png" type="image/x-icon">
</head>

<body>
    <header>
        <div class="txt-header">
            <div class="nombre-header" id="btnPerfil" data-caja="<?php echo $caja; ?>">
                <span id="headerNombreCajero"><?php echo htmlspecialchars($nombre); ?></span>
                <i class='bx bx-chevron-down'></i>
            </div>
            <div class="info-header">Caja <?php echo $caja; ?> - <?php echo $infoEvento; ?></div>
        </div>
        <div class="menu-header" id="btnAdminjero" style="display: none;">
            <i class='bx bx-menu'></i>
        </div>
    </header>

    <div class="perfil-dropdown" id="perfilDropdown">
        <div id="contenedorPerfil">
            <?php include __DIR__ . "/../endpoints/refresh_perfil.php"; ?>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="sidebar-menu" id="sidebarMenu">
        <div class="sidebar-header">
            <h2>Panel de Control</h2>
            <i class='bx bx-x' id="closeSidebar"></i>
        </div>

        <div class="sidebar-tabs">
            <button class="tab-btn active" id="btnTab1" onclick="openTab(event, 'tabCaja1')">Caja 1</button>
            <button class="tab-btn" id="btnTab2" onclick="openTab(event, 'tabCaja2')">Caja 2</button>
            <button class="tab-btn" id="btnTab3" onclick="openTab(event, 'tabResumen')">Resumen</button>
        </div>

        <div id="contenedorTabs">
            <?php include __DIR__ . "/../endpoints/refresh_navbar.php"; ?>
        </div>
    </div>

    <div class="body2" id="body2" style="display: block;">
        <div class="productos">

            <div class="categorias">
                <?php foreach ($categoriaPorID as $row): ?>
                    <div class="cat-individual <?php echo catInicial($row['nombre']); ?>"
                        id="cat-<?php echo idCategoria($row['nombre']); ?>">
                        <p><?php echo htmlspecialchars($row['nombre']); ?></p>
                        <div class="cat-cantidad" data-categoria="<?php echo idCategoria($row['nombre']); ?>"
                            style="display: none;">
                            <p>0</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="txt-cat" id="txt-cat">Medidas</p>

            <?php foreach (getCategorias() as $cat): ?>
                <div class="all-elementos" id="all-elementos-<?php echo idCategoria($cat['nombre']); ?>" <?php echo classCat($cat['nombre']); ?>>
                    <?php foreach (getProductos($evento_id, (int)$cat['id']) as $row): ?>
                        <div class="div-cat" data-id="<?php echo (int) $row['id']; ?>"
                            data-precio="<?php echo $row['precio']; ?>">
                            <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                            <p class="elemento-titulo"><?php echo htmlspecialchars($row['nombre']); ?></p>
                            <p class="elemento-precio">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <!-- CORTESIAS -->
            <p class="txt-cat" id="txt-cat-cortesia-consumiciones" style="display:none">Consumiciones</p>

            <div class="all-elementos" id="all-elementos-cortesias-consumiciones"
                style="display: none; margin-bottom: 10px;">
                <div class="div-cat div-consumo" data-id="26x1-free" data-precio="0" data-cantidad="1">
                    <p class="elemento-consumo">x1</p>
                    <img src="imgs/consumicion.png" alt="" style="display: none;">
                    <p class="elemento-titulo" style="display: none;">CONSUMO</p>
                </div>
                <div class="div-cat div-consumo" data-id="26x5-free" data-precio="0" data-cantidad="5">
                    <p class="elemento-consumo">x5</p>
                    <img src="imgs/consumicion.png" alt="" style="display: none;">
                    <p class="elemento-titulo" style="display: none;">CONSUMO</p>
                </div>
                <div class="div-cat div-consumo" data-id="26x10-free" data-precio="0" data-cantidad="10">
                    <p class="elemento-consumo">x10</p>
                    <img src="imgs/consumicion.png" alt="" style="display: none;">
                    <p class="elemento-titulo" style="display: none;">CONSUMO</p>
                </div>
                <div class="div-cat div-consumo" data-id="26x50-free" data-precio="0" data-cantidad="50">
                    <p class="elemento-consumo">x50</p>
                    <img src="imgs/consumicion.png" alt="" style="display: none;">
                    <p class="elemento-titulo" style="display: none;">CONSUMO</p>
                </div>
            </div>

            <p class="txt-cat" id="txt-cat-cortesia-medidas" style="display:none">Medidas</p>
            <div class="all-elementos" id="all-elementos-cortesias-medidas" style="display: none; margin-bottom: 10px;">
                <?php foreach (getProductos($evento_id, 1) as $row): ?>
                    <div class="div-cat" data-id="<?php echo (int) $row['id']; ?>-free" data-precio="0">
                        <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                        <p class="elemento-titulo"><?php echo htmlspecialchars($row['nombre']); ?></p>
                        <p class="elemento-precio">GRATIS</p>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="txt-cat" id="txt-cat-cortesia-botellas" style="display:none;">Botellas</p>
            <div class="all-elementos" id="all-elementos-cortesias-botellas"
                style="display: none; margin-bottom: 10px;">
                <?php foreach (getProductos($evento_id, 2) as $row): ?>
                    <div class="div-cat" data-id="<?php echo (int) $row['id']; ?>-free" data-precio="0">
                        <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                        <p class="elemento-titulo"><?php echo htmlspecialchars($row['nombre']); ?></p>
                        <p class="elemento-precio">GRATIS</p>
                    </div>
                <?php endforeach; ?>
            </div>

            <p class="txt-cat" id="txt-cat-cortesia-varios" style="display:none">Varios</p>
            <div class="all-elementos" id="all-elementos-cortesias-varios" style="display: none; margin-bottom: 80px;">
                <?php foreach (getProductos($evento_id, 3) as $row): ?>
                    <div class="div-cat" data-id="<?php echo (int) $row['id']; ?>-free" data-precio="0">
                        <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                        <p class="elemento-titulo"><?php echo htmlspecialchars($row['nombre']); ?></p>
                        <p class="elemento-precio">GRATIS</p>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <div class="btn-cobrar" id="btn-cobrar" style="display:none;">
            <p class="cobrar-izq">Cobrar - 0 tickets</p>
            <p class="cobrar-der">$0</p>
        </div>
    </div>

    <div class="body3" id="body3" style="display: none;">
        <div class="nav-header">
            <button class="btn-back" id="btn-back"> &lt; Volver</button>
            <h2>Editar pedido</h2>
        </div>

        <div class="product-list" id="productList"></div>

        <div class="total-row">
            <span>Total</span>
            <span id="totalAmount">$0</span>
        </div>

        <div class="payment-section">
            <p class="payment-label">Método de pago</p>
            <div class="payment-buttons">
                <button class="btn-payment" id="btn-efectivo">Efectivo</button>
                <button class="btn-payment" id="btn-tarjeta">Tarjeta</button>
            </div>
        </div>
    </div>

    <div class="body4" id="body4" style="display: none;">
        <div class="aprobado">
            <img src="imgs/aprobado-logo.png" alt="">
            <p class="venta-registrada">Venta registrada</p>
            <p class="info-venta">Operación #5142 - Efectivo - $1.460</p>
        </div>
        <div class="botones-venta">
            <div class="nueva-venta" id="btn-nuevaventa">
                <p>Nueva venta</p>
            </div>
            <div class="reimprimir-tickets" id="btn-reimprimirtickets">
                <p>Reimprimir tickets</p>
            </div>
        </div>
    </div>
    
    <!-- Simulador de Impresión Térmica 58mm (Modo Prueba) -->
    <div class="modal-tck-overlay" id="modalTicketsOverlay" style="display: none;">
        <div class="modal-tck-box">
            <div class="modal-tck-header">
                <span id="modalTicketsTitulo">Vista previa 58mm (0 tickets)</span>
                <button type="button" class="btn-cerrar-tck" id="btnCerrarModalTickets">Cerrar</button>
            </div>
            <div class="modal-tck-rollo" id="contenedorTickets58mm"></div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="scripts/scriptSistema.js"></script>
    <script src="scripts/scriptProductos.js"></script>
    <script src="scripts/scriptNavbar.js"></script>
<script>
    async function calcularTamanoTicket() {
    const ticket = document.querySelector(".ticket-58mm");

    if (!ticket) return;

    await document.fonts.ready;

    await Promise.all(
        Array.from(ticket.querySelectorAll("img")).map(img => {
            if (img.complete) return Promise.resolve();

            return new Promise(resolve => {
                img.addEventListener("load", resolve, { once: true });
                img.addEventListener("error", resolve, { once: true });
            });
        })
    );

    // Obtener la altura del ticket en píxeles
    const alturaPx = ticket.getBoundingClientRect().height;

    // Convertir píxeles CSS a milímetros
    const alturaMm = alturaPx * 25.4 / 96;

    // Agregar 3 mm de seguridad
    const alturaFinal = Math.ceil(alturaMm + 3);

    let estilo = document.getElementById("pagina-dinamica");

    if (!estilo) {
        estilo = document.createElement("style");
        estilo.id = "pagina-dinamica";
        document.head.appendChild(estilo);
    }

    estilo.textContent = `
        @page {
            size: 58mm ${alturaFinal}mm;
            margin: 0;
        }
    `;

    console.log(
        `Ticket configurado: 58mm x ${alturaFinal}mm`
    );
}
window.addEventListener("load", calcularTamanoTicket);

window.addEventListener("beforeprint", () => {
    const ticket = document.querySelector(".ticket");
    if (!ticket) return;

    const alturaMm =
        ticket.getBoundingClientRect().height * 25.4 / 96;

    const alturaFinal = Math.ceil(alturaMm + 3);

    let estilo = document.getElementById("pagina-dinamica");

    if (!estilo) {
        estilo = document.createElement("style");
        estilo.id = "pagina-dinamica";
        document.head.appendChild(estilo);
    }

    estilo.textContent = `
        @page {
            size: 58mm ${alturaFinal}mm;
            margin: 0;
        }
    `;
});
</script>
</body>
</html>