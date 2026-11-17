document.addEventListener("DOMContentLoaded", () => {
  const btnCobrar = document.getElementById("btn-cobrar");
  const btnEfectivo = document.getElementById("btn-efectivo");
  const btnTarjeta = document.getElementById("btn-tarjeta");
  const btnNuevaVenta = document.getElementById("btn-nuevaventa");
  const btnBack = document.getElementById("btn-back");
  const btnReimprimirTickets = document.getElementById("btn-reimprimirtickets");

  const body2 = document.getElementById("body2");
  const body3 = document.getElementById("body3");
  const body4 = document.getElementById("body4");
  const infoVentaEl = document.querySelector(".info-venta");

  const modalTicketsOverlay = document.getElementById("modalTicketsOverlay");
  const contenedorTickets58mm = document.getElementById("contenedorTickets58mm");
  const modalTicketsTitulo = document.getElementById("modalTicketsTitulo");
  const btnCerrarModalTickets = document.getElementById("btnCerrarModalTickets");

  let procesandoVenta = false;
  let ultimaVenta = null;

  if (btnCerrarModalTickets && modalTicketsOverlay) {
    btnCerrarModalTickets.addEventListener("click", () => {
      modalTicketsOverlay.style.display = "none";
    });

    modalTicketsOverlay.addEventListener("click", (e) => {
      if (e.target === modalTicketsOverlay) {
        modalTicketsOverlay.style.display = "none";
      }
    });
  }

  function mostrarPreviewTickets58mm(datosVenta, esReimpresion = false) {
    if (!modalTicketsOverlay || !contenedorTickets58mm || !datosVenta?.tickets) return;

    const ahora = new Date();
    const fechaHora =
      ahora.toLocaleDateString("es-UY") +
      " " +
      ahora.toLocaleTimeString("es-UY", { hour: "2-digit", minute: "2-digit" });

    const totalTck = datosVenta.tickets.length;
    modalTicketsTitulo.textContent = esReimpresion
      ? `Reimpresión 58mm (${totalTck} ticket${totalTck > 1 ? "s" : ""})`
      : `Ticket 58mm (${totalTck} ticket${totalTck > 1 ? "s" : ""})`;

    contenedorTickets58mm.innerHTML = "";

    datosVenta.tickets.forEach((ticket, idx) => {
      const esCortesia = Number(ticket.precio) === 0;
      const precioTxt = esCortesia
        ? "CORTESIA"
        : "$" + Number(ticket.precio).toLocaleString("es-UY");

      const papel = document.createElement("div");
      papel.className = "ticket-58mm";
      papel.innerHTML = `
        ${esReimpresion ? '<div class="tck58-reimp">*** REIMPRESION ***</div>' : ""}
        <div class="tck58-evento">AFTER BANCO 10.12</div>
        <div class="tck58-linea">--------------------------------</div>
        <div class="tck58-etiqueta">${ticket.etiqueta}</div>
        <div class="tck58-precio">${precioTxt}</div>
        <div class="tck58-qr" id="qr58-${idx}"></div>
        <div class="tck58-numero">${ticket.numero_ticket}</div>
        <div class="tck58-linea">--------------------------------</div>
        <div class="tck58-meta">Op #${datosVenta.numero_operacion} - ${datosVenta.caja_nombre}</div>
        <div class="tck58-meta">Cajero: ${datosVenta.cajero_nombre}</div>
        <div class="tck58-meta">${fechaHora}</div>
        <div class="tck58-corte">✂ - - - - CORTE DE PAPEL - - - -</div>
      `;

      contenedorTickets58mm.appendChild(papel);

      const qrContainer = document.getElementById(`qr58-${idx}`);
      if (typeof QRCode !== "undefined") {
        new QRCode(qrContainer, {
          text: ticket.codigo_qr,
          width: 115,
          height: 115,
          colorDark: "#000000",
          colorLight: "#ffffff",
          correctLevel: QRCode.CorrectLevel.M,
        });
      } else {
        qrContainer.innerHTML = `<div class="tck58-qr-fallback">QR: ${ticket.codigo_qr.slice(0, 14)}...</div>`;
      }
    });

    modalTicketsOverlay.style.display = "flex";
  }

  btnCobrar.addEventListener("click", (e) => {
    e.preventDefault();
    if (carrito.length === 0) return;

    renderizarPedido();

    body2.style.display = "none";
    body3.style.display = "block";

    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  btnBack.addEventListener("click", (e) => {
    e.preventDefault();

    sincronizarCards();

    body3.style.display = "none";
    body2.style.display = "block";

    window.scrollTo({ top: 0, behavior: "smooth" });
  });

  async function registrarVenta(metodoPago) {
    if (procesandoVenta || carrito.length === 0) return;

    procesandoVenta = true;
    btnEfectivo.disabled = true;
    btnTarjeta.disabled = true;

    const btnActivo = metodoPago === "efectivo" ? btnEfectivo : btnTarjeta;
    const textoOriginal = btnActivo.textContent;
    btnActivo.textContent = "Procesando...";

    try {
      const response = await fetch("procesar_venta.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          accion: "registrar",
          metodo_pago: metodoPago,
          items: carrito,
        }),
      });

      const rawText = await response.text();
      let data;
      try {
        data = JSON.parse(rawText);
      } catch {
        throw new Error("Respuesta inválida del servidor: " + rawText.slice(0, 200));
      }

      if (data.status === "success") {
        ultimaVenta = data;

        const metodoTxt = metodoPago === "efectivo" ? "Efectivo" : "Tarjeta";
        const totalTxt = "$" + Number(data.total).toLocaleString("es-UY");
        if (infoVentaEl) {
          infoVentaEl.textContent = `Operación #${data.numero_operacion} - ${metodoTxt} - ${totalTxt}`;
        }

        body3.style.display = "none";
        body4.style.display = "block";

        if (data.impresion?.modo_prueba) {
          mostrarPreviewTickets58mm(data, false);
        } else if (data.impresion && !data.impresion.ok) {
          alert("Venta guardada, pero revisá la impresora: " + data.impresion.error);
        }

        if (typeof actualizarPanelLateral === "function") {
          actualizarPanelLateral();
        }
        if (typeof actualizarPanelPerfil === "function") {
          actualizarPanelPerfil();
        }
      } else {
        alert(data.message || "No se pudo registrar la venta.");
      }
    } catch (error) {
      console.error("Error al procesar venta:", error);
      alert(error.message || "Error de conexión al registrar la venta.");
    } finally {
      procesandoVenta = false;
      btnEfectivo.disabled = false;
      btnTarjeta.disabled = false;
      btnActivo.textContent = textoOriginal;
    }
  }

  btnEfectivo.addEventListener("click", (e) => {
    e.preventDefault();
    registrarVenta("efectivo");
  });

  btnTarjeta.addEventListener("click", (e) => {
    e.preventDefault();
    registrarVenta("tarjeta");
  });

  btnNuevaVenta.addEventListener("click", (e) => {
    e.preventDefault();

    ultimaVenta = null;
    if (modalTicketsOverlay) modalTicketsOverlay.style.display = "none";
    seleccionarMedidas();
    limpiarCarrito();
    mostrarBtnCobrar();

    body4.style.display = "none";
    body2.style.display = "block";
  });

  btnReimprimirTickets.addEventListener("click", async (e) => {
    e.preventDefault();
    if (!ultimaVenta) return;

    const textoOriginal = btnReimprimirTickets.querySelector("p").textContent;
    btnReimprimirTickets.querySelector("p").textContent = "Reimprimiendo...";

    try {
      const response = await fetch("procesar_venta.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          accion: "reimprimir",
          venta_id: ultimaVenta.venta_id,
        }),
      });

      const rawText = await response.text();
      const data = JSON.parse(rawText);

      if (data.status === "success") {
        if (data.impresion?.modo_prueba) {
          mostrarPreviewTickets58mm(data, true);
        }
      } else {
        alert(data.message || "Error al reimprimir.");
      }
    } catch (error) {
      console.error("Error registrando reimpresión:", error);
      alert(error.message || "Error de conexión al reimprimir.");
    } finally {
      btnReimprimirTickets.querySelector("p").textContent = textoOriginal;
    }
  });
});