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

document.addEventListener("DOMContentLoaded", () => {
    // Referencias al DOM
    const menuIcon = document.querySelector('.menu-header i');
    const sidebar = document.getElementById('sidebarMenu');
    const overlay = document.getElementById('sidebarOverlay');
    const closeBtn = document.getElementById('closeSidebar');

    // Abrir Menú
    menuIcon.addEventListener('click', () => {
        sidebar.classList.add('open');
        overlay.style.display = 'block';
    });

    // Cerrar Menú
    function closeMenu() {
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
    }
    closeBtn.addEventListener('click', closeMenu);
    overlay.addEventListener('click', closeMenu);

    // Sistema de Pestañas
    function openTab(evt, tabName) {
        const tabContents = document.getElementsByClassName("tab-content");
        for (let i = 0; i < tabContents.length; i++) {
            tabContents[i].style.display = "none";
        }
        const tabBtns = document.getElementsByClassName("tab-btn");
        for (let i = 0; i < tabBtns.length; i++) {
            tabBtns[i].className = tabBtns[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }

    // Acciones de Cajero (requerirán endpoints PHP para ejecutarse en la BD)
    function cambiarNombre(cajeroId, cajaNum) {
        const nuevoNombre = prompt("Ingresa el nuevo nombre y apellido:");
        if (nuevoNombre) {
            // Aquí colocar fetch a un archivo PHP (ej. api_cajero.php?accion=cambiar_nombre)
            console.log(`Cambiando nombre de cajero ${cajeroId} a: ${nuevoNombre}`);
            // Simular actualización en el front:
            document.getElementById(`nombreCajero${cajaNum}`).textContent = nuevoNombre;
        }
    }

    function resetPin(cajeroId) {
        if (confirm("¿Estás seguro de que deseas restablecer el PIN de este cajero?")) {
            const nuevoPin = prompt("Ingresa el nuevo PIN de 4 dígitos:");
            if (nuevoPin && nuevoPin.length === 4) {
                // Aquí fetch a PHP (ej. api_cajero.php?accion=reset_pin)
                console.log(`PIN del cajero ${cajeroId} cambiado a: ${nuevoPin}`);
                alert("PIN actualizado correctamente.");
            }
        }
    }

    function reimprimir(ventaId) {
        console.log(`Enviando orden a la impresora para la venta ID: ${ventaId}`);
        // Aquí llamar a  función/endpoint de impresión
        alert(`Reimprimiendo ticket de la venta #${ventaId}...`);
    }
    // Función para recargar el panel lateral (AJAX)
function actualizarPanelLateral() {
    // 1. Detectar qué pestaña está abierta actualmente para no desorientar al usuario
    let tabActiva = "tabCaja1"; 
    let tabBtnActivo = document.getElementById("btnTab1");

    if (document.getElementById("tabCaja2") && document.getElementById("tabCaja2").style.display === "block") {
        tabActiva = "tabCaja2";
        tabBtnActivo = document.getElementById("btnTab2");
    } else if (document.getElementById("tabResumen") && document.getElementById("tabResumen").style.display === "block") {
        tabActiva = "tabResumen";
        tabBtnActivo = document.getElementById("btnTab3");
    }

    // 2. Traer el nuevo HTML
    fetch('refresh_sidebar.php')
        .then(response => response.text())
        .then(html => {
            // Reemplazar todo el contenido
            document.getElementById("contenedorTabs").innerHTML = html;
            // Volver a abrir la pestaña que el usuario estaba mirando
            openTab({ currentTarget: tabBtnActivo }, tabActiva);
        })
        .catch(error => console.error('Error al actualizar el panel:', error));
}

    // Función para cambiar nombre
    function cambiarNombre(cajeroId, cajaNum) {
        let nuevoNombre = prompt("Ingrese el nuevo nombre para el cajero de la Caja " + cajaNum + ":");
        
        // Si cancela o deja vacío, no hacemos nada
        if (!nuevoNombre || nuevoNombre.trim() === "") return; 

        let formData = new FormData();
        formData.append("accion", "cambiar_nombre");
        formData.append("cajero_id", cajeroId);
        formData.append("nombre", nuevoNombre.trim());

        fetch("acciones_cajero.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    actualizarPanelLateral(); // <--- Aquí actualizamos en tiempo real
                } else {
                    alert("Error al actualizar: " + data.message);
                }
            });
    }

    // Función para restablecer PIN
    function resetPin(cajeroId) {
        let nuevoPin = prompt("Ingrese el nuevo PIN (se recomiendan 4 dígitos):");
        
        if (!nuevoPin || nuevoPin.trim() === "") return;

        let formData = new FormData();
        formData.append("accion", "reset_pin");
        formData.append("cajero_id", cajeroId);
        formData.append("pin", nuevoPin.trim());

        fetch("acciones_cajero.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    alert("PIN actualizado correctamente.");
                } else {
                    alert("Error al actualizar el PIN.");
                }
            });
    }
});
