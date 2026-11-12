const catMedidas = document.getElementById("cat-medidas");
const catBotellas = document.getElementById("cat-botellas");
const catVarios = document.getElementById("cat-varios");
const catCortesias = document.getElementById("cat-cortesias");

const txtCategoria = document.getElementById("txt-cat");
const txtCatCortesiaMedidas = document.getElementById(
  "txt-cat-cortesia-medidas",
);
const txtCatCortesiaBotellas = document.getElementById(
  "txt-cat-cortesia-botellas",
);
const txtCatCortesiaVarios = document.getElementById("txt-cat-cortesia-varios");

const allElementosMedidas = document.getElementById("all-elementos-medidas");
const allElementosBotellas = document.getElementById("all-elementos-botellas");
const allElementosVarios = document.getElementById("all-elementos-varios");
const allElementosCortesiasMedidas = document.getElementById(
  "all-elementos-cortesias-medidas",
);
const allElementosCortesiasBotellas = document.getElementById(
  "all-elementos-cortesias-botellas",
);
const allElementosCortesiasVarios = document.getElementById(
  "all-elementos-cortesias-varios",
);

function limpiarSeleccion() {
  document.querySelectorAll(".cat-individual").forEach((cat) => {
    cat.classList.remove("cat-selected");
  });
}

function seleccionarMedidas() {
  limpiarSeleccion();
  catMedidas.classList.add("cat-selected");

  txtCategoria.style.display = "block";
  txtCategoria.textContent = "Medidas";

  allElementosMedidas.style.display = "grid";
  allElementosBotellas.style.display = "none";
  allElementosVarios.style.display = "none";
  allElementosCortesiasMedidas.style.display = "none";
  allElementosCortesiasBotellas.style.display = "none";
  allElementosCortesiasVarios.style.display = "none";

  txtCatCortesiaMedidas.style.display = "none";
  txtCatCortesiaBotellas.style.display = "none";
  txtCatCortesiaVarios.style.display = "none";
}

function seleccionarBotellas() {
  limpiarSeleccion();
  catBotellas.classList.add("cat-selected");

  txtCategoria.style.display = "block";
  txtCategoria.textContent = "Botellas";

  allElementosMedidas.style.display = "none";
  allElementosBotellas.style.display = "grid";
  allElementosVarios.style.display = "none";
  allElementosCortesiasMedidas.style.display = "none";
  allElementosCortesiasBotellas.style.display = "none";
  allElementosCortesiasVarios.style.display = "none";

  txtCatCortesiaMedidas.style.display = "none";
  txtCatCortesiaBotellas.style.display = "none";
  txtCatCortesiaVarios.style.display = "none";
}

function seleccionarVarios() {
  limpiarSeleccion();
  catVarios.classList.add("cat-selected");

  txtCategoria.style.display = "block";
  txtCategoria.textContent = "Varios";

  allElementosMedidas.style.display = "none";
  allElementosBotellas.style.display = "none";
  allElementosVarios.style.display = "grid";
  allElementosCortesiasMedidas.style.display = "none";
  allElementosCortesiasBotellas.style.display = "none";
  allElementosCortesiasVarios.style.display = "none";

  txtCatCortesiaMedidas.style.display = "none";
  txtCatCortesiaBotellas.style.display = "none";
  txtCatCortesiaVarios.style.display = "none";
}

function seleccionarCortesias() {
  limpiarSeleccion();
  catCortesias.classList.add("cat-selected");

  txtCategoria.style.display = "none";

  allElementosMedidas.style.display = "none";
  allElementosBotellas.style.display = "none";
  allElementosVarios.style.display = "none";
  allElementosCortesiasMedidas.style.display = "grid";
  allElementosCortesiasBotellas.style.display = "grid";
  allElementosCortesiasVarios.style.display = "grid";

  txtCatCortesiaMedidas.style.display = "block";
  txtCatCortesiaBotellas.style.display = "block";
  txtCatCortesiaVarios.style.display = "block";
}

catMedidas.addEventListener("click", seleccionarMedidas);
catBotellas.addEventListener("click", seleccionarBotellas);
catVarios.addEventListener("click", seleccionarVarios);
catCortesias.addEventListener("click", seleccionarCortesias);

const carrito = []; // [{ id, nombre, precio, cantidad }]

function sumarProducto(card) {
  const id = card.dataset.id;
  let item = carrito.find((p) => p.id === id);

  if (item) {
    item.cantidad++;
  } else {
    const cat = card.closest('.all-elementos').id
        .replace('all-elementos-', '')
        .replace('cortesias-', '')
        .replace(/s$/, '');

    item = {
        id: id,
        nombre: card.querySelector('.elemento-titulo').textContent,
        categoria: cat.charAt(0).toUpperCase() + cat.slice(1),
        precio: Number(card.dataset.precio),
        imagen: card.querySelector('img').getAttribute('src'),
        cantidad: 1
    };
        carrito.push(item);
    }

  actualizarCard(card, item);
  actualizarTotal();
  mostrarBtnCobrar();
}

function restarProducto(card) {
  const id = card.dataset.id;
  const index = carrito.findIndex((p) => p.id === id);
  if (index === -1) return;

  const item = carrito[index];
  item.cantidad--;

  if (item.cantidad <= 0) {
    carrito.splice(index, 1); // se quita del array
    actualizarCard(card, null);
  } else {
    actualizarCard(card, item);
  }

  actualizarTotal();
  mostrarBtnCobrar();
}

// Dibuja (o quita) selected, cantidad y restar en la tarjeta
function actualizarCard(card, item) {
  let cantidad = card.querySelector(".elemento-cantidad");
  let restar = card.querySelector(".elemento-restar");

  if (!item) {
    card.classList.remove("elemento-selected");
    if (cantidad) cantidad.remove();
    if (restar) restar.remove();
    return;
  }

  card.classList.add("elemento-selected");

  if (!cantidad) {
    cantidad = document.createElement("div");
    cantidad.className = "elemento-cantidad";
    cantidad.innerHTML = "<p></p>";
    card.appendChild(cantidad);
  }
  cantidad.querySelector("p").textContent = item.cantidad;

  if (!restar) {
    restar = document.createElement("div");
    restar.className = "elemento-restar";
    restar.innerHTML = "<p>-</p>";
    card.appendChild(restar);
  }
}

function mostrarBtnCobrar() {
  const btnCobrar = document.getElementById("btn-cobrar");

  if (carrito.length < 1) {
    btnCobrar.style.display = "none";
  } else {
    btnCobrar.style.display = "flex";
  }
}

// Opcional: actualiza el botón "Cobrar"
function actualizarTotal() {
  const tickets = carrito.reduce((acc, p) => acc + p.cantidad, 0);
  const total = carrito.reduce((acc, p) => acc + p.precio * p.cantidad, 0);
  const totalTxt = "$" + total.toLocaleString("es-UY");

  document.querySelector(".cobrar-izq").textContent =
    `Cobrar - ${tickets} tickets`;
  document.querySelector(".cobrar-der").textContent = totalTxt;
  document.getElementById("totalAmount").textContent = totalTxt;
}

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".all-elementos").forEach((contenedor) => {
    contenedor.addEventListener("click", (e) => {
      const card = e.target.closest(".div-cat");
      if (!card) return;

      if (e.target.closest(".elemento-restar")) {
        restarProducto(card);
      } else {
        sumarProducto(card);
      }
    });
  });
});

function renderizarPedido() {
  const lista = document.getElementById("productList");
  lista.innerHTML = "";

  carrito.forEach((item) => {
    const esCortesia = String(item.id).endsWith("free");
    const esMedida = item.categoria === "Medida";

    const nombreMostrado = esCortesia
    ? esMedida
        ? `CORTESÍA ${item.categoria} ${item.nombre}`
        : `CORTESÍA ${item.nombre}`
    : esMedida
        ? `${item.categoria} ${item.nombre}`
        : item.nombre;

    const precioMostrado = esCortesia ? "GRATIS" : `$${item.precio.toLocaleString('es-UY')}`;

    const div = document.createElement("div");
    div.className = "product-item";
    div.dataset.id = item.id;
    div.innerHTML = `
        <img src="${item.imagen}" alt="${item.nombre}" class="product-img">
        <div class="product-details">
            <span class="product-name">${nombreMostrado}</span>
            <span class="product-price">${precioMostrado}</span>
        </div>
        <div class="product-controls">
            <button class="btn-circle btn-minus">-</button>
            <span class="product-qty">${item.cantidad}</span>
            <button class="btn-circle btn-plus">+</button>
        </div>
    `;
    lista.appendChild(div);
  });

  const total = carrito.reduce((acc, p) => {
    const precio = Number(p.precio) || 0;
    const cantidad = Number(p.cantidad) || 0;
    return acc + precio * cantidad;
  }, 0);

  document.getElementById("totalAmount").textContent =
    "$" + total.toLocaleString("es-UY");
}

document.getElementById("productList").addEventListener("click", (e) => {
  const fila = e.target.closest(".product-item");
  if (!fila) return;

  const index = carrito.findIndex((p) => p.id === fila.dataset.id);
  if (index === -1) return;

  if (e.target.closest(".btn-plus")) {
    carrito[index].cantidad++;
  } else if (e.target.closest(".btn-minus")) {
    carrito[index].cantidad--;
    if (carrito[index].cantidad <= 0) carrito.splice(index, 1);
  } else {
    return;
  }

  renderizarPedido(); // actualiza lista y total de body3
  actualizarTotal(); // actualiza el botón "Cobrar" de body2
});

function sincronizarCards() {
  document.querySelectorAll(".all-elementos .div-cat").forEach((card) => {
    const item = carrito.find((p) => p.id === card.dataset.id);
    actualizarCard(card, item || null);
  });
}

function limpiarCarrito() {
  carrito.length = 0; // vacía el array

  sincronizarCards(); // saca selected, cantidad y restar de las tarjetas
  renderizarPedido(); // deja vacía la lista de body3
  actualizarTotal(); // actualiza textos y total (y llama a mostrarBtnCobrar)
}
