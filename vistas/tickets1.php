<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/estilostickets.css">
    <title>Document</title>
</head>
<body>

    <div class="ticket">
        <div class="codigohead">
            <p>514210123</p>
        </div>
        <div class="evento">
            <img src="imgs/logo2.png" alt="" class="logoevento">
            <p class="nombreevento">AFTER BANCO</p>
        </div>
        <div class="separador">
            <p>--------------------</p>
        </div>
        <div class="consumo">
            <p class="tituloconsumo">1x MEDIDA FERNET</p>
            <p class="precioconsumo">$ 300.00</p>
            <div class="barcode">
                <img src="imgs/barcode.gif" alt="">
            </div>
        </div>
        <div class="separador">
            <p>--------------------</p>
        </div>
    </div>

</body>

<script>
async function calcularTamanoTicket() {
    const ticket = document.querySelector(".ticket");

    if (!ticket) return;

    // Esperar a que carguen las fuentes
    await document.fonts.ready;

    // Esperar a que carguen las imágenes
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

    // Crear o actualizar el tamaño de impresión
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

// Calcular automáticamente al cargar
window.addEventListener("load", calcularTamanoTicket);

// Recalcular antes de imprimir con Ctrl + P
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

</html>