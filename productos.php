<?php
include_once "funciones_navbar.php";
include_once "categorias_interfaz.php";
include_once "productos_interfaz.php";
?>
<!DOCTYPE html>
<html lang="es-UY">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ticketeo</title>
    <link rel="stylesheet" href="estilos.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="icon" href="imgs/ticketeo-icon.png" type="image/x-icon">
</head>

<body>
    <header>
        <div class="txt-header">
            <div class="nombre-header">Juan Cardozo</div>
            <div class="info-header">Caja 1 - After Banco 10.12</div>
        </div>
        <div class="menu-header">
            <i class='bx bx-menu'></i>
        </div>
    </header>

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
            <?php include "refresh_sidebar.php"; ?>
        </div>

    </div>


    <div class="body2" id="body2" style="display: block;">
        <div class="productos">

            <div class="categorias">
                <?php foreach ($categoriaPorID as $row) { ?>

                    <div class="cat-individual <?php echo catInicial($row['nombre']); ?>"
                        id="cat-<?php echo idCategoria($row['nombre']); ?>">
                        <p><?php echo $row['nombre']; ?></p>
                        <div class="cat-cantidad" data-categoria="<?php echo idCategoria($row['nombre']); ?>" style="display: none;">
                            <p>0</p>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <p class="txt-cat" id="txt-cat">Medidas</p>

            <?php foreach (getCategorias() as $cat): ?>
                <div class="all-elementos" id="all-elementos-<?php echo idCategoria($cat['nombre']); ?>" <?php echo classCat($cat['nombre']) ?>>

                    <?php foreach (getProductos(1, $cat['id']) as $row): ?>
                        <div class="div-cat" data-id="<?php echo (int) $row['id']; ?>"
                            data-precio="<?php echo $row['precio']; ?>">
                            <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                            <p class="elemento-titulo"><?php echo htmlspecialchars($row['nombre']); ?></p>
                            <p class="elemento-precio">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></p>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endforeach; ?>

            <!-- CORTESIAS-->
            <p class="txt-cat" id="txt-cat-cortesia-consumiciones" style="display:none">Consumiciones</p>

            <div class="all-elementos" id="all-elementos-cortesias-consumiciones" style="display: none; margin-bottom: 10px;">
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

            <?php foreach (getCategorias() as $cat): ?>
                <div class="all-elementos" id="all-elementos-cortesias-medidas" style="display: none;  margin-bottom: 10px;">

                    <?php foreach (getProductos(1, 1) as $row): ?>
                        <div class="div-cat" data-id="<?php echo (int) $row['id']; ?>-free"
                            data-precio="0">
                            <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                            <p class="elemento-titulo"><?php echo htmlspecialchars($row['nombre']); ?></p>
                            <p class="elemento-precio">GRATIS</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>


            <p class="txt-cat" id="txt-cat-cortesia-botellas" style="display:none;">Botellas</p>
            <?php foreach (getCategorias() as $cat): ?>
                <div class="all-elementos" id="all-elementos-cortesias-botellas" style="display: none;  margin-bottom: 10px;">

                    <?php foreach (getProductos(1, 2) as $row): ?>
                        <div class="div-cat" data-id="<?php echo (int) $row['id']; ?>-free"
                            data-precio="0">
                            <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                            <p class="elemento-titulo"><?php echo htmlspecialchars($row['nombre']); ?></p>
                            <p class="elemento-precio">GRATIS</p>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endforeach; ?>

            <p class="txt-cat" id="txt-cat-cortesia-varios" style="display:none">Varios</p>
            <?php foreach (getCategorias() as $cat): ?>
                <div class="all-elementos" id="all-elementos-cortesias-varios" style="display: none;  margin-bottom: 80px;">

                    <?php foreach (getProductos(1, 3) as $row): ?>
                        <div class="div-cat" data-id="<?php echo (int) $row['id']; ?>-free"
                            data-precio="0">
                            <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                            <p class="elemento-titulo"><?php echo htmlspecialchars($row['nombre']); ?></p>
                            <p class="elemento-precio">GRATIS</p>
                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endforeach; ?>
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

    <script>
        function openTab(evt, tabName) {
            // Ocultar todos los contenidos de las pestañas
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            // Quitar la clase "active" de todos los botones
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }

            // Mostrar la pestaña actual y añadir "active" al botón clickeado
            document.getElementById(tabName).style.display = "block";
            if (evt) {
                evt.currentTarget.classList.add("active");
            }
        }
    </script>

    <script src="scriptSistema.js"></script>
    <script src="scriptProductos.js"></script>
</body>

</html>