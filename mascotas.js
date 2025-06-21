document.addEventListener("DOMContentLoaded", () => {
  const mascotasPorPagina = 6;
  let paginaActual = 1;
  let mascotas = [];

  const contenedorMascotas = document.getElementById("mascotas-lista");
  const paginacion = document.getElementById("pagination");
  const searchInput = document.getElementById("search-input");
  const sortSelect = document.getElementById("sort-select");

  // Elementos del modal
  const modalMascota = document.getElementById("modal-mascota");
  const btnCerrarModalMascota = document.getElementById("btn-cerrar-modal-mascota");

  const modalImg = document.getElementById("modal-imagen-mascota");
  const modalTitulo = document.getElementById("modal-titulo-mascota");
  const modalEdad = document.getElementById("modal-edad-mascota");
  const modalRaza = document.getElementById("modal-raza-mascota");
  const modalTamano = document.getElementById("modal-tamano-mascota");
  const modalDescripcion = document.getElementById("modal-descripcion-mascota");
  const modalUsuario = document.getElementById("modal-usuario-mascota");
  const modalPrecio = document.getElementById("modal-precio-mascota");

  function cargarMascotas() {
    fetch("get_mascotas.php")
      .then(response => response.json())
      .then(data => {
        if (data.success && Array.isArray(data.mascotas)) {
          mascotas = data.mascotas;
          mostrarMascotas();
        } else {
          contenedorMascotas.innerHTML = `<p style="text-align:center; color:#666;">${data.message || "No se pudieron cargar las mascotas."}</p>`;
        }
      })
      .catch(error => {
        console.error("Error al cargar mascotas:", error);
        contenedorMascotas.innerHTML = "<p style='color:red; text-align:center;'>Error al cargar las mascotas.</p>";
      });
  }

  function filtrarYOrdenarMascotas() {
    const texto = searchInput.value.toLowerCase();

    let filtradas = mascotas.filter(m =>
      (m.nombre && m.nombre.toLowerCase().includes(texto)) ||
      (m.raza && m.raza.toLowerCase().includes(texto))
    );

    const orden = sortSelect.value;
    if (orden === "name-asc") {
      filtradas.sort((a, b) => (a.nombre || "").localeCompare(b.nombre || ""));
    } else if (orden === "name-desc") {
      filtradas.sort((a, b) => (b.nombre || "").localeCompare(a.nombre || ""));
    }

    return filtradas;
  }

  function mostrarMascotas() {
    const filtradas = filtrarYOrdenarMascotas();
    const totalPaginas = Math.ceil(filtradas.length / mascotasPorPagina);
    if (paginaActual > totalPaginas) paginaActual = totalPaginas || 1;

    const inicio = (paginaActual - 1) * mascotasPorPagina;
    const fin = inicio + mascotasPorPagina;
    const visibles = filtradas.slice(inicio, fin);

    contenedorMascotas.innerHTML = "";

    if (visibles.length === 0) {
      contenedorMascotas.innerHTML = "<p style='text-align:center;'>No se encontraron mascotas que coincidan con la búsqueda.</p>";
      paginacion.innerHTML = "";
      return;
    }

    visibles.forEach((m, index) => {
      const imagen = m.imagen || "https://via.placeholder.com/300x200?text=Sin+imagen";
      const edad = m.edad || "-";
      const raza = m.raza || "-";
      const usuario = m.usuario_nombre || "Desconocido";

      const div = document.createElement("div");
      div.className = "producto-item mascota-item";
      div.setAttribute("tabindex", "0");
      div.setAttribute("role", "button");
      div.setAttribute("aria-label", `Mascota: ${m.nombre}`);
      div.innerHTML = `
        <img loading="lazy" src="${imagen}" alt="${m.nombre || 'Mascota'}" />
        <h2>${m.nombre || "Sin nombre"}</h2>
        <p><strong>Edad:</strong> ${edad} años</p>
        <p><strong>Raza:</strong> ${raza}</p>
        <p><small><strong>Subido por:</strong> ${usuario}</small></p>
      `;

      div.addEventListener("click", () => {
        abrirModalMascota(m);
      });

      contenedorMascotas.appendChild(div);
    });

    mostrarPaginacion(totalPaginas);
  }

  function mostrarPaginacion(totalPaginas) {
    paginacion.innerHTML = "";
    if (totalPaginas <= 1) return;

    const btnPrev = document.createElement("button");
    btnPrev.textContent = "Anterior";
    btnPrev.disabled = paginaActual === 1;
    btnPrev.addEventListener("click", () => {
      paginaActual--;
      mostrarMascotas();
    });
    paginacion.appendChild(btnPrev);

    for (let i = 1; i <= totalPaginas; i++) {
      const btn = document.createElement("button");
      btn.textContent = i;
      btn.disabled = i === paginaActual;
      btn.classList.toggle("active", i === paginaActual);
      btn.addEventListener("click", () => {
        paginaActual = i;
        mostrarMascotas();
      });
      paginacion.appendChild(btn);
    }

    const btnNext = document.createElement("button");
    btnNext.textContent = "Siguiente";
    btnNext.disabled = paginaActual === totalPaginas;
    btnNext.addEventListener("click", () => {
      paginaActual++;
      mostrarMascotas();
    });
    paginacion.appendChild(btnNext);
  }

  // Abrir modal
  function abrirModalMascota(m) {
    modalImg.src = m.imagen || "https://via.placeholder.com/300x200?text=Sin+imagen";
    modalTitulo.textContent = m.nombre || "Sin nombre";
    modalEdad.textContent = m.edad || "-";
    modalRaza.textContent = m.raza || "-";
    modalTamano.textContent = m.tamano || "-";
    modalDescripcion.textContent = m.descripcion || "-";
    modalUsuario.textContent = m.usuario_nombre || "Desconocido";
    modalPrecio.textContent = m.precio ? `$${parseFloat(m.precio).toFixed(2)}` : "No disponible";

    modalMascota.style.display = "flex";
  }

  // Cerrar modal
  btnCerrarModalMascota.addEventListener("click", () => {
    modalMascota.style.display = "none";
  });

  window.addEventListener("click", (e) => {
    if (e.target === modalMascota) {
      modalMascota.style.display = "none";
    }
  });

  // Eventos de búsqueda y orden
  searchInput.addEventListener("input", () => {
    paginaActual = 1;
    mostrarMascotas();
  });

  sortSelect.addEventListener("change", () => {
    paginaActual = 1;
    mostrarMascotas();
  });

  // Iniciar
  cargarMascotas();
});
