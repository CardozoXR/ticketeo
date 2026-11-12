<?php
include "categorias_interfaz.php";
include "productos_interfaz.php";
?>
<!DOCTYPE html>
<html lang="es-UY">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ticketeo</title>
    <link rel="stylesheet" href="estilos.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
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
    
    <div class="body2" id="body2" style="display: block;">
        <div class="productos">

            <div class="categorias">
                <?php
                    foreach ($categoriaPorID as $row) {
                ?>
                
                <div class="cat-individual <?php echo catInicial($row['nombre']); ?>" id="cat-<?php echo idCategoria($row['nombre']); ?>">
                    <p><?php echo $row['nombre'] ?></p>
                </div>

                <?php
                    }
                ?>
            </div>

            <p class="txt-cat" id="txt-cat">Medidas</p>
            <!-- MEDIDAS-->
            <!-- <div class="all-elementos" id="all-elementos-medidas" style="display: grid;">
                <div class="div-cat" data-id="fernet" data-precio="250">
                    <img src="imgs/fernet-logo.png" alt="">
                    <p class="elemento-titulo">Fernet</p>
                    <p class="elemento-precio">$250</p>
                </div>
                <div class="div-cat" data-id="gin" data-precio="250">
                    <img src="imgs/gin-logo.png" alt="">
                    <p class="elemento-titulo">Gin</p>
                    <p class="elemento-precio">$250</p>
                </div>
                <div class="div-cat" data-id="vodka" data-precio="250">
                    <img src="imgs/skyy-logo.png" alt="">
                    <p class="elemento-titulo">Vodka</p>
                    <p class="elemento-precio">$250</p>
                </div>
                <div class="div-cat" data-id="ron" data-precio="250">
                    <img src="imgs/bacardi-logo.png" alt="">
                    <p class="elemento-titulo">Ron</p>
                    <p class="elemento-precio">$250</p>
                </div>
                <div class="div-cat" data-id="tequila" data-precio="200">
                    <img src="imgs/elcharro-logo.png" alt="">
                    <p class="elemento-titulo">Tequila</p>
                    <p class="elemento-precio">$200</p>
                </div>
                <div class="div-cat" data-id="whisky-red" data-precio="250">
                    <img src="imgs/redlabel-logo.png" alt="">
                    <p class="elemento-titulo">Whisky Red</p>
                    <p class="elemento-precio">$250</p>
                </div>
                <div class="div-cat" data-id="jagger" data-precio="300">
                    <img src="imgs/jagger-logo.png" alt="">
                    <p class="elemento-titulo">Jagger</p>
                    <p class="elemento-precio">$300</p>
                </div>
                <div class="div-cat" data-id="gin" data-precio="300">
                    <img src="imgs/bombay-logo.png" alt="">
                    <p class="elemento-titulo">Gin Bombay</p>
                    <p class="elemento-precio">$300</p>
                </div>
            </div> -->
                <?php foreach (getCategorias() as $cat): ?>
                    <div class="all-elementos" id="all-elementos-<?php echo idCategoria($cat['nombre']); ?>">

                        <?php foreach (getProductos(1, $cat['id']) as $row): ?>
                            <div class="div-cat"
                                data-id="<?php echo (int)$row['id']; ?>"
                                data-precio="<?php echo $row['precio']; ?>">
                                <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                                <p class="elemento-titulo"><?php echo htmlspecialchars($row['nombre']); ?></p>
                                <p class="elemento-precio">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></p>
                            </div>
                        <?php endforeach; ?>

                    </div>
                <?php endforeach; ?>
            <!-- BOTELLAS-->
            <!-- <div class="all-elementos" id="all-elementos-botellas" style="display: none;">
                <div class="div-cat" data-id="fernet" data-precio="250">
                    <img src="imgs/fernet-botella.png" alt="">
                    <p class="elemento-titulo">Botella Fernet</p>
                    <p class="elemento-precio">$2.500</p>
                </div>
                <div class="div-cat" data-id="gin" data-precio="250">
                    <img src="imgs/gin-botella.png" alt="">
                    <p class="elemento-titulo">Botella Gin</p>
                    <p class="elemento-precio">$2.500</p>
                </div>
                <div class="div-cat" data-id="vodka" data-precio="250">
                    <img src="imgs/skyy-botella.png" alt="">
                    <p class="elemento-titulo">Botella Vodka</p>
                    <p class="elemento-precio">$2.500</p>
                </div>
                <div class="div-cat" data-id="ron" data-precio="250">
                    <img src="imgs/bacardi-botella.png" alt="">
                    <p class="elemento-titulo">Botella Ron</p>
                    <p class="elemento-precio">$2.500</p>
                </div>
                <div class="div-cat" data-id="tequila" data-precio="200">
                    <img src="imgs/elcharro-botella.png" alt="">
                    <p class="elemento-titulo">Botella Tequila</p>
                    <p class="elemento-precio">$2.500</p>
                </div>
                <div class="div-cat" data-id="whisky-red" data-precio="250">
                    <img src="imgs/redlabel-botella.png" alt="">
                    <p class="elemento-titulo">Botella Whisky Red</p>
                    <p class="elemento-precio">$2.500</p>
                </div>
                <div class="div-cat" data-id="jagger" data-precio="350">
                    <img src="imgs/jagger-botella.png" alt="">
                    <p class="elemento-titulo">Botella Jagger</p>
                    <p class="elemento-precio">$3.200</p>
                </div>
                <div class="div-cat" data-id="bombay" data-precio="350">
                    <img src="imgs/bombay-botella.png" alt="">
                    <p class="elemento-titulo">Botella Gin Bombay</p>
                    <p class="elemento-precio">$3.000</p>
                </div>
                <div class="div-cat" data-id="champagne" data-precio="350">
                    <img src="imgs/champagne-botella.png" alt="">
                    <p class="elemento-titulo">Botella Champagne</p>
                    <p class="elemento-precio">$2.000</p>
                </div>
                <div class="div-cat" data-id="whisky-black" data-precio="350">
                    <img src="imgs/blacklabel-botella.png" alt="">
                    <p class="elemento-titulo">Botella Whisky Black</p>
                    <p class="elemento-precio">$3.500</p>
                </div>
            </div> -->
            <!-- VARIOS-->
            <!-- <div class="all-elementos" id="all-elementos-varios" style="display: none;">
                <div class="div-cat" data-id="fernet" data-precio="250">
                    <img src="imgs/cocacola-logo.png" alt="">
                    <p class="elemento-titulo">Refresco</p>
                    <p class="elemento-precio">$100</p>
                </div>
                <div class="div-cat" data-id="gin" data-precio="250">
                    <img src="imgs/cocacola-botella.png" alt="">
                    <p class="elemento-titulo">Refresco 1.5L</p>
                    <p class="elemento-precio">$400</p>
                </div>
                <div class="div-cat" data-id="vodka" data-precio="250">
                    <img src="imgs/nativa-logo.png" alt="">
                    <p class="elemento-titulo">Agua 600ml</p>
                    <p class="elemento-precio">$100</p>
                </div>
                <div class="div-cat" data-id="ron" data-precio="250">
                    <img src="imgs/speed-logo.png" alt="">
                    <p class="elemento-titulo">Speed</p>
                    <p class="elemento-precio">$250</p>
                </div>
                <div class="div-cat" data-id="tequila" data-precio="200">
                    <img src="imgs/schneider-logo.png" alt="">
                    <p class="elemento-titulo">Cerveza Lata</p>
                    <p class="elemento-precio">$250</p>
                </div>
                <div class="div-cat" data-id="whisky-red" data-precio="250">
                    <img src="imgs/ecovaso.png" alt="">
                    <p class="elemento-titulo">Revaso</p>
                    <p class="elemento-precio">$70</p>
                </div>
                <div class="div-cat" data-id="jagger" data-precio="350">
                    <img src="imgs/aperol-logo.png" alt="">
                    <p class="elemento-titulo">Copa Aperol</p>
                    <p class="elemento-precio">$100</p>
                </div>
            </div> -->

            <!-- CORTESIAS-->
            <p class="txt-cat" id="txt-cat-cortesia-medidas" style="display:none">Medidas</p>
            <div class="all-elementos" id="all-elementos-cortesias-medidas" style="display: none; margin-bottom: 10px;">
                <div class="div-cat" data-id="fernet" data-precio="250">
                    <img src="imgs/fernet-logo.png" alt="">
                    <p class="elemento-titulo">Fernet</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="gin" data-precio="250">
                    <img src="imgs/gin-logo.png" alt="">
                    <p class="elemento-titulo">Gin</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="vodka" data-precio="250">
                    <img src="imgs/skyy-logo.png" alt="">
                    <p class="elemento-titulo">Vodka</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="ron" data-precio="250">
                    <img src="imgs/bacardi-logo.png" alt="">
                    <p class="elemento-titulo">Ron</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="tequila" data-precio="200">
                    <img src="imgs/elcharro-logo.png" alt="">
                    <p class="elemento-titulo">Tequila</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="whisky-red" data-precio="250">
                    <img src="imgs/redlabel-logo.png" alt="">
                    <p class="elemento-titulo">Whisky Red</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="jagger" data-precio="350">
                    <img src="imgs/jagger-logo.png" alt="">
                    <p class="elemento-titulo">Jagger</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
            </div>
            <p class="txt-cat" id="txt-cat-cortesia-botellas" style="display:none;">Botellas</p>
            <div class="all-elementos" id="all-elementos-cortesias-botellas" style="display: none;  margin-bottom: 10px;">
                <div class="div-cat" data-id="fernet" data-precio="250">
                    <img src="imgs/fernet-botella.png" alt="">
                    <p class="elemento-titulo">Botella Fernet</p>
                    <p class="elemento-precio">FREE</p>
                </div>
                <div class="div-cat" data-id="gin" data-precio="250">
                    <img src="imgs/gin-botella.png" alt="">
                    <p class="elemento-titulo">Botella Gin</p>
                    <p class="elemento-precio">FREE</p>
                </div>
                <div class="div-cat" data-id="vodka" data-precio="250">
                    <img src="imgs/skyy-botella.png" alt="">
                    <p class="elemento-titulo">Botella Vodka</p>
                    <p class="elemento-precio">FREE</p>
                </div>
                <div class="div-cat" data-id="ron" data-precio="250">
                    <img src="imgs/bacardi-botella.png" alt="">
                    <p class="elemento-titulo">Botella Ron</p>
                    <p class="elemento-precio">FREE</p>
                </div>
                <div class="div-cat" data-id="tequila" data-precio="200">
                    <img src="imgs/elcharro-botella.png" alt="">
                    <p class="elemento-titulo">Botella Tequila</p>
                    <p class="elemento-precio">FREE</p>
                </div>
                <div class="div-cat" data-id="whisky-red" data-precio="250">
                    <img src="imgs/redlabel-botella.png" alt="">
                    <p class="elemento-titulo">Botella Whisky Red</p>
                    <p class="elemento-precio">FREE</p>
                </div>
                <div class="div-cat" data-id="jagger" data-precio="350">
                    <img src="imgs/jagger-botella.png" alt="">
                    <p class="elemento-titulo">Botella Jagger</p>
                    <p class="elemento-precio">FREE</p>
                </div>
                <div class="div-cat" data-id="bombay" data-precio="350">
                    <img src="imgs/bombay-botella.png" alt="">
                    <p class="elemento-titulo">Botella Gin Bombay</p>
                    <p class="elemento-precio">FREE</p>
                </div>
                <div class="div-cat" data-id="champagne" data-precio="350">
                    <img src="imgs/champagne-botella.png" alt="">
                    <p class="elemento-titulo">Botella Champagne</p>
                    <p class="elemento-precio">FREE</p>
                </div>
                <div class="div-cat" data-id="whisky-black" data-precio="350">
                    <img src="imgs/blacklabel-botella.png" alt="">
                    <p class="elemento-titulo">Botella Whisky Black</p>
                    <p class="elemento-precio">FREE</p>
                </div>
            </div>
            <p class="txt-cat" id="txt-cat-cortesia-varios" style="display:none">Varios</p>
            <div class="all-elementos" id="all-elementos-cortesias-varios" style="display: none;">
                <div class="div-cat" data-id="fernet" data-precio="250">
                    <img src="imgs/cocacola-logo.png" alt="">
                    <p class="elemento-titulo">Refresco</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="gin" data-precio="250">
                    <img src="imgs/cocacola-botella.png" alt="">
                    <p class="elemento-titulo">Refresco 1.5L</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="vodka" data-precio="250">
                    <img src="imgs/nativa-logo.png" alt="">
                    <p class="elemento-titulo">Agua 600ml</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="ron" data-precio="250">
                    <img src="imgs/speed-logo.png" alt="">
                    <p class="elemento-titulo">Speed</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="tequila" data-precio="200">
                    <img src="imgs/schneider-logo.png" alt="">
                    <p class="elemento-titulo">Cerveza Lata</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="whisky-red" data-precio="250">
                    <img src="imgs/ecovaso.png" alt="">
                    <p class="elemento-titulo">Revaso</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
                <div class="div-cat" data-id="jagger" data-precio="350">
                    <img src="imgs/aperol-logo.png" alt="">
                    <p class="elemento-titulo">Copa Aperol</p>
                    <p class="elemento-precio">GRATIS</p>
                </div>
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

        <div class="product-list" id="productList">
        
        <div class="product-item" data-price="250">
            <img src="imgs/fernet-logo.png" alt="Fernet" class="product-img">
            <div class="product-details">
            <span class="product-name">Fernet</span>
            <span class="product-price">$250</span>
            </div>
            <div class="product-controls">
            <button class="btn-circle btn-minus">-</button>
            <span class="product-qty">3</span>
            <button class="btn-circle btn-plus">+</button>
            </div>
        </div>

        <div class="product-item" data-price="250">
            <img src="imgs/bacardi-logo.png" alt="Ron" class="product-img">
            <div class="product-details">
            <span class="product-name">Ron</span>
            <span class="product-price">$250</span>
            </div>
            <div class="product-controls">
            <button class="btn-circle btn-minus">-</button>
            <span class="product-qty">1</span>
            <button class="btn-circle btn-plus">+</button>
            </div>
        </div>

        <div class="product-item" data-price="250">
            <img src="imgs/redlabel-logo.png" alt="Whisky" class="product-img">
            <div class="product-details">
            <span class="product-name">Whisky Red</span>
            <span class="product-price">$250</span>
            </div>
            <div class="product-controls">
            <button class="btn-circle btn-minus">-</button>
            <span class="product-qty">1</span>
            <button class="btn-circle btn-plus">+</button>
            </div>
        </div>

        <div class="product-item" data-price="70">
            <img src="imgs/ecovaso.png" alt="Ecovaso" class="product-img">
            <div class="product-details">
            <span class="product-name">Ecovaso</span>
            <span class="product-price">$70</span>
            </div>
            <div class="product-controls">
            <button class="btn-circle btn-minus">-</button>
            <span class="product-qty">3</span>
            <button class="btn-circle btn-plus">+</button>
            </div>
        </div>

        </div>

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
            <div class="nueva-venta" id="btn-nuevaventa"><p>Nueva venta</p></div>
            <div class="reimprimir-tickets" id="btn-reimprimirtickets"><p>Reimprimir tickets</p></div>
        </div>
    </div>
    
<script src="scriptSistema.js"></script>
<script src="scriptProductos.js"></script>
</body>
</html>