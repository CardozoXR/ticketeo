-- 1. Cajas (Añadido el campo 'nombre')
INSERT IGNORE INTO cajas (id, numero, nombre, evento_id) VALUES 
(1, 1, 'Caja Barra 1', 1), 
(2, 2, 'Caja VIP', 1);

-- 2. Cajeros 
INSERT IGNORE INTO cajeros (id, nombre, caja_id, pin, activo) VALUES 
(1, 'Juan Cardozo', 1, '1234', 1),
(2, 'Maria Lopez', 2, '5678', 1);

-- 3. Ventas (Añadido 'cajero_id' y 'numero_operacion')
INSERT IGNORE INTO ventas (id, caja_id, cajero_id, evento_id, numero_operacion, metodo_pago, total, cantidad_tickets, estado) VALUES 
(1, 1, 1, 1, 'OP-001', 'efectivo', 1200, 2, 'completada'),
(2, 1, 1, 1, 'OP-002', 'tarjeta', 3500, 1, 'completada'),
(3, 2, 2, 1, 'OP-003', 'efectivo', 800, 1, 'completada');

-- 4. Detalle de venta (Añadido 'etiqueta_ticket')
INSERT IGNORE INTO detalle_venta (id, venta_id, producto_evento_id, nombre_producto, etiqueta_ticket, cantidad, precio_unitario, subtotal) VALUES 
(1, 1, 24, 'Revaso', 'Vaso', 2, 100, 200),
(2, 1, 5, 'Fernet Branca', 'Bebida', 1, 1000, 1000),
(3, 2, 8, 'Whisky', 'Bebida', 1, 3500, 3500),
(4, 3, 15, 'Cerveza', 'Bebida', 1, 800, 800),
(5, 3, 99, 'Pase Libre', 'Cortesia', 1, 0, 0);

-- 5. Tickets (Añadido 'detalle_venta_id' y 'codigo_qr')
INSERT IGNORE INTO tickets (id, venta_id, detalle_venta_id, numero_ticket, codigo_qr, estado) VALUES 
(1, 1, 1, 'TCK-0001', 'qr-0001', 'valido'),
(2, 1, 2, 'TCK-0002', 'qr-0002', 'valido'),
(3, 2, 3, 'TCK-0003', 'qr-0003', 'valido'),
(4, 3, 4, 'TCK-0004', 'qr-0004', 'valido');