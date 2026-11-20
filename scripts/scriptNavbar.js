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