document.addEventListener("DOMContentLoaded", () => {
  const contenedor = document.getElementById("productos-lista");
  const paginacion = document.getElementById("pagination");

  const modal = document.getElementById("modal-producto");
  const modalImg = document.getElementById("modal-imagen");
  const modalTitulo = document.getElementById("modal-titulo");
  const modalDescripcion = document.getElementById("modal-descripcion");
  const modalPrecio = document.getElementById("modal-precio");

  const btnCerrarModal = document.getElementById("btn-cerrar-modal");
  const btnFavorito = document.getElementById("btn-favorito");
  const btnComprar = document.getElementById("btn-comprar");
  const btnGuardar = document.getElementById("btn-guardar");

  const productosPorPagina = 6;
  let paginaActual = 1;
  let productos = [];

  // Mostrar la paginación
  function mostrarPaginacion(totalPaginas) {
    paginacion.innerHTML = "";

    if (totalPaginas <= 1) return;

    const btnPrev = document.createElement("button");
    btnPrev.textContent = "Anterior";
    btnPrev.disabled = paginaActual === 1;
    btnPrev.addEventListener("click", () => {
      if (paginaActual > 1) {
        paginaActual--;
        mostrarProductos();
      }
    });
    paginacion.appendChild(btnPrev);

    for (let i = 1; i <= totalPaginas; i++) {
      const btn = document.createElement("button");
      btn.textContent = i;
      btn.disabled = i === paginaActual;
      btn.classList.toggle("active", i === paginaActual);
      btn.addEventListener("click", () => {
        paginaActual = i;
        mostrarProductos();
      });
      paginacion.appendChild(btn);
    }

    const btnNext = document.createElement("button");
    btnNext.textContent = "Siguiente";
    btnNext.disabled = paginaActual === totalPaginas;
    btnNext.addEventListener("click", () => {
      if (paginaActual < totalPaginas) {
        paginaActual++;
        mostrarProductos();
      }
    });
    paginacion.appendChild(btnNext);
  }

  // Renderizar los productos en la página
  function mostrarProductos() {
    const totalPaginas = Math.ceil(productos.length / productosPorPagina);
    if (paginaActual > totalPaginas) paginaActual = totalPaginas || 1;

    const inicio = (paginaActual - 1) * productosPorPagina;
    const fin = inicio + productosPorPagina;
    const productosPaginados = productos.slice(inicio, fin);

    contenedor.innerHTML = "";

    if (productosPaginados.length === 0) {
      contenedor.innerHTML = "<p style='text-align:center; color:#666;'>No hay productos disponibles.</p>";
      paginacion.innerHTML = "";
      return;
    }

    productosPaginados.forEach(prod => {
      const div = document.createElement("div");
      div.classList.add("producto-item");
      div.setAttribute("tabindex", "0");
      div.setAttribute("role", "button");
      div.setAttribute("aria-pressed", "false");

      const imagenSrc = prod.imagen || "https://via.placeholder.com/300x200?text=Sin+imagen";
      const nombre = prod.nombre || "Sin título";
      const descripcion = prod.descripcion || "Sin descripción";
      const precio = prod.precio ? `$${prod.precio}` : "$0.00";
      const usuarioNombre = prod.usuario_nombre || "Desconocido";

      div.innerHTML = `
        <img src="${imagenSrc}" alt="${nombre}">
        <h3>${nombre}</h3>
        <p>${descripcion}</p>
        <p><strong>Precio:</strong> ${precio}</p>
        <p><small>Publicado por: ${usuarioNombre}</small></p>
      `;

      // Abrir modal con Enter o click
      div.addEventListener("click", () => abrirModal(prod));
      div.addEventListener("keydown", e => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          abrirModal(prod);
        }
      });

      contenedor.appendChild(div);
    });

    mostrarPaginacion(totalPaginas);
  }

  // Abrir modal y asignar eventos a botones dinámicamente
  function abrirModal(prod) {
    const imagenSrc = prod.imagen || "https://via.placeholder.com/300x200?text=Sin+imagen";
    modalImg.src = imagenSrc;
    modalImg.alt = prod.nombre || "Imagen producto";
    modalTitulo.textContent = prod.nombre || "Sin título";
    modalDescripcion.textContent = prod.descripcion || "Sin descripción";
    modalPrecio.textContent = prod.precio ? `$${prod.precio}` : "$0.00";

    modal.style.display = "flex";

    // Desactivar aria-pressed antes
    btnFavorito.setAttribute("aria-pressed", "false");
    btnComprar.setAttribute("aria-pressed", "false");
    btnGuardar.setAttribute("aria-pressed", "false");

    // Asignar acciones a botones con closure para mantener el producto actual
    btnFavorito.onclick = () => alert(`Agregaste "${prod.nombre}" a favoritos.`);
    btnComprar.onclick = () => alert(`Iniciaste la compra de "${prod.nombre}".`);
    btnGuardar.onclick = () => alert(`Guardaste "${prod.nombre}".`);
  }

  // Cerrar modal
  function cerrarModal() {
    modal.style.display = "none";
    btnFavorito.onclick = null;
    btnComprar.onclick = null;
    btnGuardar.onclick = null;
  }

  // Cargar productos desde backend
  fetch("get_productos.php")
    .then(res => res.json())
    .then(data => {
      if (data.success === false && data.message === "No autenticado") {
        alert("Debes iniciar sesión para ver los productos.");
        window.location.href = "login.html";
        return;
      }

      if (data.success === true && Array.isArray(data.productos)) {
        productos = data.productos;
        mostrarProductos();
      } else if (Array.isArray(data)) {
        // En caso que get_productos.php retorne solo array sin wrapper
        productos = data;
        mostrarProductos();
      } else {
        contenedor.innerHTML = "<p style='text-align:center; color:#666;'>No hay productos disponibles.</p>";
      }
    })
    .catch(err => {
      console.error("Error al cargar productos:", err);
      contenedor.innerHTML = "<p style='text-align:center; color:#f00;'>Error cargando productos.</p>";
    });

  // Eventos para cerrar modal
  btnCerrarModal.addEventListener("click", cerrarModal);
  window.addEventListener("click", e => {
    if (e.target === modal) {
      cerrarModal();
    }
  });

  // También cerrar modal con ESC
  window.addEventListener("keydown", e => {
    if (e.key === "Escape" && modal.style.display === "flex") {
      cerrarModal();
    }
  });
});
