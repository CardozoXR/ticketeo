-- AGREGAR CONSUMICION PARA LA CATEGORIA CORTESIAS

INSERT INTO ticketeo.productos (categoria_id, nombre, img, activo)
VALUES (4, 'Consumición', 'imgs/consumicion.png', 1);

INSERT INTO ticketeo.productos_evento (evento_id, producto_id, precio, etiqueta, orden, disponible)
VALUES (1, 26, 0, 'CONSUMO', 1, 1);