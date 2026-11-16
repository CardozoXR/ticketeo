-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-11-2026 a las 05:48:26
-- Versión del servidor: 8.0.36
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ticketeo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajas`
--

CREATE TABLE `cajas` (
  `id` int UNSIGNED NOT NULL,
  `evento_id` int UNSIGNED NOT NULL,
  `numero` int UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `impresora` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cajeros`
--

CREATE TABLE `cajeros` (
  `id` int UNSIGNED NOT NULL,
  `caja_id` int UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `pin` varchar(10) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `orden` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `orden`) VALUES
(1, 'Medidas', 1),
(2, 'Botellas', 2),
(3, 'Varios', 3),
(4, 'Cortesías', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id` int UNSIGNED NOT NULL,
  `venta_id` int UNSIGNED NOT NULL,
  `producto_evento_id` int UNSIGNED NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `etiqueta_ticket` varchar(100) NOT NULL,
  `cantidad` int UNSIGNED NOT NULL DEFAULT '1',
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `eventos`
--

CREATE TABLE `eventos` (
  `id` int UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `fecha` date NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `estado` enum('borrador','activo','cerrado') NOT NULL DEFAULT 'borrador',
  `creado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `eventos`
--

INSERT INTO `eventos` (`id`, `nombre`, `fecha`, `logo`, `estado`, `creado`) VALUES
(1, 'After Banco', '2026-12-10', 'logo.png', 'activo', '2026-11-12 01:16:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int UNSIGNED NOT NULL,
  `categoria_id` int UNSIGNED NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `img` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `categoria_id`, `nombre`, `img`, `activo`) VALUES
(1, 1, 'Fernet', 'imgs/fernet-logo.png', 1),
(2, 1, 'Gin', 'imgs/gin-logo.png', 1),
(3, 1, 'Vodka', 'imgs/skyy-logo.png', 1),
(4, 1, 'Ron', 'imgs/bacardi-logo.png', 1),
(5, 1, 'Tequila', 'imgs/elcharro-logo.png', 1),
(6, 1, 'Whisky Red', 'imgs/redlabel-logo.png', 1),
(7, 1, 'Jagger', 'imgs/jagger-logo.png', 1),
(8, 1, 'Gin Bombay', 'imgs/bombay-logo.png', 1),
(9, 2, 'Botella Fernet', 'imgs/fernet-botella.png', 1),
(10, 2, 'Botella Gin', 'imgs/gin-botella.png', 1),
(11, 2, 'Botella Vodka', 'imgs/skyy-botella.png', 1),
(12, 2, 'Botella Ron', 'imgs/bacardi-botella.png', 1),
(13, 2, 'Botella Tequila', 'imgs/elcharro-botella.png', 1),
(14, 2, 'Botella Whisky Red', 'imgs/redlabel-botella.png', 1),
(15, 2, 'Botella Jagger', 'imgs/jagger-botella.png', 1),
(16, 2, 'Botella Gin Bombay', 'imgs/bombay-botella.png', 1),
(17, 2, 'Botella Champagne', 'imgs/champagne-botella.png', 1),
(18, 2, 'Botella Whisky Black', 'imgs/blacklabel-botella.png', 1),
(19, 3, 'Refresco', 'imgs/cocacola-logo.png', 1),
(20, 3, 'Refresco 1.5L', 'imgs/cocacola-botella.png', 1),
(21, 3, 'Agua 600ml', 'imgs/nativa-logo.png', 1),
(22, 3, 'Speed', 'imgs/speed-logo.png', 1),
(23, 3, 'Cerveza Lata', 'imgs/schneider-logo.png', 1),
(24, 3, 'Revaso', 'imgs/ecovaso.png', 1),
(25, 3, 'Copa Aperol', 'imgs/aperol-logo.png', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_evento`
--

CREATE TABLE `productos_evento` (
  `id` int UNSIGNED NOT NULL,
  `evento_id` int UNSIGNED NOT NULL,
  `producto_id` int UNSIGNED NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `etiqueta` varchar(100) NOT NULL COMMENT 'Texto que imprime el ticket',
  `orden` int NOT NULL DEFAULT '0',
  `disponible` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `productos_evento`
--

INSERT INTO `productos_evento` (`id`, `evento_id`, `producto_id`, `precio`, `etiqueta`, `orden`, `disponible`) VALUES
(1, 1, 1, 250.00, 'Fernet', 1, 1),
(2, 1, 2, 250.00, 'Gin', 2, 1),
(3, 1, 3, 250.00, 'Vodka', 3, 1),
(4, 1, 4, 250.00, 'Ron', 4, 1),
(5, 1, 5, 200.00, 'Tequila', 5, 1),
(6, 1, 6, 250.00, 'Whisky Red', 6, 1),
(7, 1, 7, 300.00, 'Jagger', 7, 1),
(8, 1, 8, 300.00, 'Gin Bombay', 8, 1),
(9, 1, 9, 2500.00, 'Botella Fernet', 1, 1),
(10, 1, 10, 2500.00, 'Botella Gin', 2, 1),
(11, 1, 11, 2500.00, 'Botella Vodka', 3, 1),
(12, 1, 12, 2500.00, 'Botella Ron', 4, 1),
(13, 1, 13, 2500.00, 'Botella Tequila', 5, 1),
(14, 1, 14, 2500.00, 'Botella Whisky Red', 6, 1),
(15, 1, 15, 3200.00, 'Botella Jagger', 7, 1),
(16, 1, 16, 3000.00, 'Botella Gin Bombay', 8, 1),
(17, 1, 17, 2000.00, 'Botella Champagne', 9, 1),
(18, 1, 18, 3500.00, 'Botella Whisky Black', 10, 1),
(19, 1, 19, 100.00, 'Refresco', 1, 1),
(20, 1, 20, 400.00, 'Refresco 1.5L', 2, 1),
(21, 1, 21, 100.00, 'Agua 600ml', 3, 1),
(22, 1, 22, 250.00, 'Speed', 4, 1),
(23, 1, 23, 250.00, 'Cerveza Lata', 5, 1),
(24, 1, 24, 70.00, 'Revaso', 6, 1),
(25, 1, 25, 100.00, 'Copa Aperol', 7, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_impresiones`
--

CREATE TABLE `registro_impresiones` (
  `id` int UNSIGNED NOT NULL,
  `venta_id` int UNSIGNED NOT NULL,
  `ticket_id` int UNSIGNED NOT NULL,
  `cajero_id` int UNSIGNED NOT NULL,
  `tipo` enum('original','reimpresion') NOT NULL DEFAULT 'original',
  `exitosa` tinyint(1) NOT NULL DEFAULT '1',
  `creado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id` int UNSIGNED NOT NULL,
  `cajero_id` int UNSIGNED NOT NULL,
  `caja_id` int UNSIGNED NOT NULL,
  `iniciada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id` int UNSIGNED NOT NULL,
  `venta_id` int UNSIGNED NOT NULL,
  `detalle_venta_id` int UNSIGNED NOT NULL,
  `numero_ticket` varchar(30) NOT NULL,
  `codigo_qr` varchar(255) NOT NULL,
  `estado` enum('valido','canjeado','anulado') NOT NULL DEFAULT 'valido',
  `creado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int UNSIGNED NOT NULL,
  `evento_id` int UNSIGNED NOT NULL,
  `caja_id` int UNSIGNED NOT NULL,
  `cajero_id` int UNSIGNED NOT NULL,
  `numero_operacion` int UNSIGNED NOT NULL,
  `metodo_pago` enum('efectivo','tarjeta') NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cantidad_tickets` int UNSIGNED NOT NULL DEFAULT '0',
  `estado` enum('completada','anulada') NOT NULL DEFAULT 'completada',
  `creado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_caja_evento_numero` (`evento_id`,`numero`);

--
-- Indices de la tabla `cajeros`
--
ALTER TABLE `cajeros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cajeros_caja` (`caja_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dv_venta` (`venta_id`),
  ADD KEY `fk_dv_producto_evento` (`producto_evento_id`);

--
-- Indices de la tabla `eventos`
--
ALTER TABLE `eventos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_productos_categoria` (`categoria_id`);

--
-- Indices de la tabla `productos_evento`
--
ALTER TABLE `productos_evento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_producto_evento` (`evento_id`,`producto_id`),
  ADD KEY `fk_pe_producto` (`producto_id`);

--
-- Indices de la tabla `registro_impresiones`
--
ALTER TABLE `registro_impresiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ri_venta` (`venta_id`),
  ADD KEY `fk_ri_ticket` (`ticket_id`),
  ADD KEY `fk_ri_cajero` (`cajero_id`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sesiones_cajero` (`cajero_id`),
  ADD KEY `fk_sesiones_caja` (`caja_id`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_numero_ticket` (`numero_ticket`),
  ADD UNIQUE KEY `uq_codigo_qr` (`codigo_qr`),
  ADD KEY `fk_tickets_venta` (`venta_id`),
  ADD KEY `fk_tickets_detalle` (`detalle_venta_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_operacion_evento` (`evento_id`,`numero_operacion`),
  ADD KEY `fk_ventas_caja` (`caja_id`),
  ADD KEY `fk_ventas_cajero` (`cajero_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cajeros`
--
ALTER TABLE `cajeros`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `eventos`
--
ALTER TABLE `eventos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `productos_evento`
--
ALTER TABLE `productos_evento`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `registro_impresiones`
--
ALTER TABLE `registro_impresiones`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cajas`
--
ALTER TABLE `cajas`
  ADD CONSTRAINT `fk_cajas_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cajeros`
--
ALTER TABLE `cajeros`
  ADD CONSTRAINT `fk_cajeros_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `fk_dv_producto_evento` FOREIGN KEY (`producto_evento_id`) REFERENCES `productos_evento` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dv_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos_evento`
--
ALTER TABLE `productos_evento`
  ADD CONSTRAINT `fk_pe_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pe_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `registro_impresiones`
--
ALTER TABLE `registro_impresiones`
  ADD CONSTRAINT `fk_ri_cajero` FOREIGN KEY (`cajero_id`) REFERENCES `cajeros` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ri_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ri_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `fk_sesiones_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sesiones_cajero` FOREIGN KEY (`cajero_id`) REFERENCES `cajeros` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_tickets_detalle` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalle_venta` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tickets_venta` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_caja` FOREIGN KEY (`caja_id`) REFERENCES `cajas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_cajero` FOREIGN KEY (`cajero_id`) REFERENCES `cajeros` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_evento` FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
