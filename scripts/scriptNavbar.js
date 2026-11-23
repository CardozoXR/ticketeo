function openTab(evt, tabName) {
  const tabcontent = document.getElementsByClassName("tab-content");
  for (let i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  const tablinks = document.getElementsByClassName("tab-btn");
  for (let i = 0; i < tablinks.length; i++) {
    tablinks[i].classList.remove("active");
  }

  const target = document.getElementById(tabName);
  if (target) {
    target.style.display = "block";
  }
  if (evt && evt.currentTarget) {
    evt.currentTarget.classList.add("active");
  }
}

function actualizarPanelLateral() {
  let tabActiva = "tabCaja1";
  let tabBtnActivo = document.getElementById("btnTab1");

  if (document.getElementById("tabCaja2")?.style.display === "block") {
    tabActiva = "tabCaja2";
    tabBtnActivo = document.getElementById("btnTab2");
  } else if (document.getElementById("tabResumen")?.style.display === "block") {
    tabActiva = "tabResumen";
    tabBtnActivo = document.getElementById("btnTab3");
  }

  fetch("endpoints/refresh_navbar.php")
    .then((response) => response.text())
    .then((html) => {
      const contenedor = document.getElementById("contenedorTabs");
      if (contenedor) {
        contenedor.innerHTML = html;
        openTab({ currentTarget: tabBtnActivo }, tabActiva);
      }
    })
    .catch((error) => console.error("Error al actualizar el panel:", error));
}

function actualizarPanelPerfil() {
  const contenedor = document.getElementById("contenedorPerfil");
  if (!contenedor) return;

  fetch("endpoints/refresh_perfil.php")
    .then((response) => response.text())
    .then((html) => {
      contenedor.innerHTML = html;
    })
    .catch((error) => console.error("Error al actualizar el perfil:", error));
}

function abrirTabEnNavbar(cajaNum) {
  const perfilDropdown = document.getElementById("perfilDropdown");
  const sidebar = document.getElementById("sidebarMenu");
  const overlay = document.getElementById("sidebarOverlay");

  if (perfilDropdown) perfilDropdown.classList.remove("open");
  if (sidebar) sidebar.classList.add("open");
  if (overlay) overlay.style.display = "block";

  const tabName = `tabCaja${cajaNum}`;
  const btnTab = document.getElementById(`btnTab${cajaNum}`);
  if (document.getElementById(tabName)) {
    openTab({ currentTarget: btnTab }, tabName);
  }
}

function cambiarNombre(cajeroId, cajaNum) {
  const nuevoNombre = prompt("Ingrese el nuevo nombre para el cajero de la Caja " + cajaNum + ":");
  if (!nuevoNombre || nuevoNombre.trim() === "") return;

  const formData = new FormData();
  formData.append("accion", "cambiar_nombre");
  formData.append("cajero_id", cajeroId);
  formData.append("nombre", nuevoNombre.trim());

  fetch("endpoints/acciones_navbar.php", { method: "POST", body: formData })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        const cajaActual = Number(document.getElementById("btnPerfil")?.dataset.caja || 0);
        if (cajaActual === Number(cajaNum)) {
          const headerNombre = document.getElementById("headerNombreCajero");
          if (headerNombre) headerNombre.textContent = data.nuevo_nombre;
        }
        actualizarPanelLateral();
        actualizarPanelPerfil();
      } else {
        alert("Error al actualizar: " + data.message);
      }
    })
    .catch((error) => console.error("Error:", error));
}

function resetPin(cajeroId) {
  const nuevoPin = prompt("Ingrese el nuevo PIN (4 dígitos):");
  if (!nuevoPin || nuevoPin.trim() === "") return;

  const formData = new FormData();
  formData.append("accion", "reset_pin");
  formData.append("cajero_id", cajeroId);
  formData.append("pin", nuevoPin.trim());

  fetch("endpoints/acciones_navbar.php", { method: "POST", body: formData })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        alert("PIN actualizado correctamente.");
      } else {
        alert("Error: " + data.message);
      }
    })
    .catch((error) => console.error("Error:", error));
}

document.addEventListener("DOMContentLoaded", () => {
  const menuIcon = document.querySelector(".menu-header i");
  const sidebar = document.getElementById("sidebarMenu");
  const overlay = document.getElementById("sidebarOverlay");
  const closeBtn = document.getElementById("closeSidebar");

  const btnPerfil = document.getElementById("btnPerfil");
  const perfilDropdown = document.getElementById("perfilDropdown");
  const contenedorTabs = document.getElementById("contenedorTabs");

  if (btnPerfil && perfilDropdown) {
    btnPerfil.addEventListener("click", (e) => {
      e.stopPropagation();
      sidebar?.classList.remove("open");
      if (overlay) overlay.style.display = "none";
      perfilDropdown.classList.toggle("open");
    });

    perfilDropdown.addEventListener("click", (e) => {
      e.stopPropagation();
    });

    document.addEventListener("click", () => {
      perfilDropdown.classList.remove("open");
    });
  }

  menuIcon?.addEventListener("click", () => {
    if (perfilDropdown) perfilDropdown.classList.remove("open");
    sidebar?.classList.add("open");
    if (overlay) overlay.style.display = "block";
  });

  function closeMenu() {
    sidebar?.classList.remove("open");
    if (overlay) overlay.style.display = "none";
  }

  closeBtn?.addEventListener("click", closeMenu);
  overlay?.addEventListener("click", closeMenu);

  // Reimpresión desde el listado de últimas 10 ventas del panel lateral
  contenedorTabs?.addEventListener("click", async (e) => {
    const btnReimprimir = e.target.closest(".reimprimirTickets");
    if (!btnReimprimir) return;

    const ventaId = Number(btnReimprimir.dataset.ventaId || 0);
    if (!ventaId) return;

    try {
      const response = await fetch("endpoints/procesar_venta.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          accion: "reimprimir",
          venta_id: ventaId,
        }),
      });
      const data = await response.json();
      if (data.status === "success") {
        if (data.impresion?.modo_prueba && typeof window.mostrarPreviewTickets58mm === "function") {
          window.mostrarPreviewTickets58mm(data, true);
        }
      } else {
        alert(data.message || "No se pudo reimprimir la venta.");
      }
    } catch (error) {
      console.error("Error al reimprimir desde panel:", error);
    }
  });
});

  function imprimirResumenEvento(tipo = "general", cajaId = null) {
    const formData = new FormData();
    formData.append("accion", "imprimir_resumen");
    if (tipo === "caja" && cajaId) {
      formData.append("caja_filtro", cajaId);
    }

    fetch("endpoints/acciones_navbar.php", { method: "POST", body: formData })
      .then((res) => res.json())
      .then((data) => {
        if (data.status !== "success") {
          alert("Error: " + (data.message || "No se pudo generar el resumen."));
          return;
        }

        if (data.impresion?.modo_prueba) {
          mostrarPreviewResumen58mm(data.resumen);
        } else {
          alert("Resumen enviado a la impresora térmica.");
        }
      })
      .catch((err) => console.error("Error al imprimir resumen:", err));
  }

  function mostrarPreviewResumen58mm(resumen) {
    const overlay = document.getElementById("modalTicketsOverlay");
    const contenedor = document.getElementById("contenedorTickets58mm");
    const titulo = document.getElementById("modalTicketsTitulo");
    if (!overlay || !contenedor) return;

    if (titulo) titulo.textContent = resumen.titulo_head;

    const fmtDinero = (val) => "$" + Number(val || 0).toLocaleString("es-UY");
    const fmtNum = (val) => Number(val || 0).toLocaleString("es-UY");

    const infoHtml = (resumen.info_evento || [])
      .map((linea) => `<p class="txtinfo">${linea}</p>`)
      .join("");

    contenedor.innerHTML = `
      <div class="ticket">
        <div class="codigohead">
          <p>${resumen.titulo_head}</p>
        </div>

        <div class="evento">
          <img src="imgs/logo2.png" alt="Logo" class="logoevento" onerror="this.style.display='none'" />
          <p class="nombreevento">${resumen.evento_titulo}</p>
        </div>

        <div class="separador"><p>-------------------</p></div>

        <div class="infocajero">${infoHtml}</div>

        <div class="separador"><p>-------------------</p></div>

        <div class="inforesumen">
          <p class="tituloticket">RESUMEN VENTAS</p>
          <div class="infototal divuno">
            <p class="txtresumen">TOTAL:</p>
            <p class="dineroresumen">${fmtDinero(resumen.ventas.total)}</p>
          </div>
          <div class="infototal">
            <p class="txtresumen">EFECTIVO:</p>
            <p class="dineroresumen">${fmtDinero(resumen.ventas.efectivo)}</p>
          </div>
          <div class="infototal">
            <p class="txtresumen">TARJETA:</p>
            <p class="dineroresumen">${fmtDinero(resumen.ventas.tarjeta)}</p>
          </div>
        </div>

        <div class="separador"><p>-------------------</p></div>

        <div class="inforesumen">
          <p class="tituloticket">RESUMEN TICKETS</p>
          <div class="infototal divuno">
            <p class="txtresumen">TOTAL EMITIDOS:</p>
            <p class="dineroresumen">${fmtNum(resumen.tickets.total_emitidos)}</p>
          </div>
          <div class="infototal">
            <p class="txtresumen">ORIGINAL:</p>
            <p class="dineroresumen">${fmtNum(resumen.tickets.original)}</p>
          </div>
          <div class="infototal">
            <p class="txtresumen">REIMPRESIÓN:</p>
            <p class="dineroresumen">${fmtNum(resumen.tickets.reimpresion)}</p>
          </div>
        </div>

        <div class="separador"><p>-------------------</p></div>

        <div class="infogeneral">
          <p>Evento: ${resumen.evento_nombre} (${resumen.codigo_evento})</p>
          <p>Fecha: ${resumen.fecha_emision}</p>
          <p>Caja: ${resumen.caja_texto}</p>
          <p>Cajero: ${resumen.cajero_nombre}</p>
        </div>

        <div class="imgfooter">
          <img src="imgs/footer-ticketeo.png" alt="Ticketeo" onerror="this.style.display='none'" />
        </div>

        <div class="separador"><p>-------------------</p></div>
      </div>
    `;

    overlay.style.display = "flex";

    requestAnimationFrame(async () => {
      const ticketEl = contenedor.querySelector(".ticket");
      if (!ticketEl) return;
      if (document.fonts?.ready) await document.fonts.ready;

      await Promise.all(
        Array.from(ticketEl.querySelectorAll("img")).map((img) =>
          img.complete
            ? Promise.resolve()
            : new Promise((r) => {
                img.addEventListener("load", r, { once: true });
                img.addEventListener("error", r, { once: true });
              })
        )
      );

      const alturaFinal = Math.ceil((ticketEl.getBoundingClientRect().height * 25.4) / 96 + 3);
      let estilo = document.getElementById("pagina-dinamica");
      if (!estilo) {
        estilo = document.createElement("style");
        estilo.id = "pagina-dinamica";
        document.head.appendChild(estilo);
      }
      estilo.textContent = `@page { size: 58mm ${alturaFinal}mm; margin: 0; }`;
    });
  }