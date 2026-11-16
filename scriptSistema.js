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

  btnCobrar.addEventListener("click", async (e) => {
    e.preventDefault();

    renderizarPedido();

    body2.style.display = "none";
    body3.style.display = "block";

    window.scrollTo({ top: 0, instant: "smooth" });
  });

  btnBack.addEventListener("click", async (e) => {
    e.preventDefault();

    sincronizarCards();

    body3.style.display = "none";
    body2.style.display = "block";

    window.scrollTo({ top: 0, instant: "smooth" });
  });

  btnEfectivo.addEventListener("click", async (e) => {
    e.preventDefault();

    body3.style.display = "none";
    body4.style.display = "block";

    imprimirTickets();
  });

  btnTarjeta.addEventListener("click", async (e) => {
    e.preventDefault();

    body3.style.display = "none";
    body4.style.display = "block";

    imprimirTickets();
  });

  btnNuevaVenta.addEventListener("click", async (e) => {
    e.preventDefault();

    seleccionarMedidas();
    renderizarPedido();
    sincronizarCards();
    limpiarCarrito();
    mostrarBtnCobrar();

    body4.style.display = "none";
    body2.style.display = "block";
  });

  btnReimprimirTickets.addEventListener("click", async (e) => {
    e.preventDefault();

    imprimirTickets();
  });

  function imprimirTickets() {
    //logica para imprimir tickets
  }
});