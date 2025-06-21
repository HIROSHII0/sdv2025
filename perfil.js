window.addEventListener("DOMContentLoaded", () => {
  const publicacionesContainer = document.getElementById("publicaciones");
  const btnPublicar = document.getElementById("btn-publicar");
  const modalPublicar = document.getElementById("modal-publicar");
  const btnCerrarForm = document.getElementById("btn-cerrar-form");
  const formulario = document.getElementById("formulario-publicar");
  const tipoSelect = document.getElementById("tipo");
  const archivoInput = document.getElementById("archivo");
  const zonaDrop = document.getElementById("zonaDrop");
  const nombreArchivo = document.getElementById("nombre-archivo");
  const previewImagen = document.getElementById("preview-imagen");
  const barraProgreso = document.getElementById("barra-progreso");
  const btnLogout = document.getElementById("btn-logout");

  let publicaciones = [];
  let archivoSeleccionado = null;

  if (btnPublicar && modalPublicar) {
    btnPublicar.addEventListener("click", () => {
      modalPublicar.classList.remove("oculto");
    });
  }

  if (btnCerrarForm && modalPublicar && formulario) {
    btnCerrarForm.addEventListener("click", () => {
      modalPublicar.classList.add("oculto");
      resetFormulario();
    });
  }

  function resetFormulario() {
    if (!formulario) return;
    formulario.reset();
    if (previewImagen) {
      previewImagen.src = "";
      previewImagen.classList.add("oculto");
    }
    if (nombreArchivo) nombreArchivo.textContent = "Ningún archivo seleccionado";
    if (barraProgreso) {
      barraProgreso.style.display = "none";
      barraProgreso.value = 0;
    }
    archivoSeleccionado = null;
  }

  if (zonaDrop && archivoInput) {
    zonaDrop.addEventListener("click", () => archivoInput.click());

    zonaDrop.addEventListener("dragover", e => {
      e.preventDefault();
      zonaDrop.classList.add("highlight");
    });

    zonaDrop.addEventListener("dragleave", e => {
      e.preventDefault();
      zonaDrop.classList.remove("highlight");
    });

    zonaDrop.addEventListener("drop", e => {
      e.preventDefault();
      zonaDrop.classList.remove("highlight");
      if (e.dataTransfer.files.length) {
        archivoInput.files = e.dataTransfer.files;
        manejarArchivo();
      }
    });
  }

  if (archivoInput) {
    archivoInput.addEventListener("change", manejarArchivo);
  }

  function manejarArchivo() {
    if (!archivoInput || archivoInput.files.length === 0) return;
    archivoSeleccionado = archivoInput.files[0];
    if (nombreArchivo) nombreArchivo.textContent = archivoSeleccionado.name;
    if (previewImagen) {
      const reader = new FileReader();
      reader.onload = e => {
        previewImagen.src = e.target.result;
        previewImagen.classList.remove("oculto");
      };
      reader.readAsDataURL(archivoSeleccionado);
    }
  }

  async function cargarPublicaciones(categoria = 'todo') {
    if (!publicacionesContainer) return;
    publicacionesContainer.innerHTML = "<p>Cargando publicaciones...</p>";
    try {
      const res = await fetch(`get_publicaciones.php?categoria=${categoria}`, {
        credentials: "include"
      });
      if (!res.ok) throw new Error("Respuesta del servidor no OK");
      const data = await res.json();

      if (!data.success) {
        if (data.message && data.message.toLowerCase().includes("no autenticado")) {
          publicacionesContainer.innerHTML = "<p>Por favor inicia sesión para ver tus publicaciones.</p>";
        } else {
          publicacionesContainer.innerHTML = "<p>Error al cargar publicaciones.</p>";
        }
        console.error(data.message || "Error desconocido");
        return;
      }

      publicaciones = data.publicaciones || [];
      mostrarPublicaciones();
    } catch (error) {
      publicacionesContainer.innerHTML = "<p>Error al cargar publicaciones.</p>";
      console.error(error);
    }
  }

  function mostrarPublicaciones() {
    if (!publicacionesContainer) return;
    publicacionesContainer.innerHTML = publicaciones.length === 0
      ? "<p>No hay publicaciones para mostrar.</p>"
      : "";

    publicaciones.forEach(pub => {
      const imagenSrc = pub.imagen ? `uploads/${escapeHtml(pub.imagen)}` : "https://via.placeholder.com/300x200?text=Sin+imagen";
      const precioTexto = pub.precio ? `$${Number(pub.precio).toFixed(2)}` : "Precio no disponible";

      const div = document.createElement("div");
      div.classList.add("publicacion");
      div.style.border = "1px solid #ccc";
      div.style.borderRadius = "8px";
      div.style.padding = "1rem";
      div.style.marginBottom = "1rem";
      div.style.display = "flex";
      div.style.gap = "1rem";
      div.style.alignItems = "center";
      div.style.backgroundColor = "#fff";
      div.style.boxShadow = "0 2px 6px rgba(0,0,0,0.1)";

      div.innerHTML = `
        <div style="flex-shrink: 0; width: 150px; height: 100px; overflow: hidden; border-radius: 6px;">
          <img src="${imagenSrc}" alt="${escapeHtml(pub.titulo)}" style="width: 100%; height: 100%; object-fit: cover;" loading="lazy" />
        </div>
        <div style="flex-grow: 1;">
          <h3 style="margin: 0 0 0.3rem;">${escapeHtml(pub.titulo)}</h3>
          <p style="margin: 0 0 0.5rem; color: #555;">${escapeHtml(pub.descripcion)}</p>
          <p style="margin: 0 0 0.3rem; font-weight: 600; color: #00796b;">${precioTexto}</p>
          <p style="margin: 0; font-style: italic; color: #555;">Tipo: ${escapeHtml(pub.tipo)}</p>
        </div>
      `;

      publicacionesContainer.appendChild(div);
    });
  }

  function escapeHtml(text = "") {
    return text.toString().replace(/[&<>"']/g, m => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;'
    })[m]);
  }

  // Filtros: al hacer click recarga con parámetro
  document.querySelectorAll(".filtro-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      document.querySelectorAll(".filtro-btn").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      const categoria = btn.getAttribute("data-categoria");
      cargarPublicaciones(categoria);
    });
  });

  // Logout
  if (btnLogout) {
    btnLogout.addEventListener("click", () => {
      fetch("logout.php", { credentials: "include" })
        .then(res => {
          if (res.ok) location.reload();
          else alert("Error al cerrar sesión");
        })
        .catch(() => alert("Error de conexión al cerrar sesión"));
    });
  }

  // Enviar nueva publicación
  if (formulario && tipoSelect) {
    formulario.addEventListener("submit", async e => {
      e.preventDefault();

      const tipo = tipoSelect.value;
      const titulo = document.getElementById("titulo")?.value.trim() || "";
      const descripcion = document.getElementById("descripcion")?.value.trim() || "";

      let precio = "";
      if (tipo === "producto") {
        precio = document.getElementById("precio_producto")?.value.trim() || "";
      } else if (tipo === "servicio") {
        precio = document.getElementById("precio")?.value.trim() || "";
      }

      if (!tipo || !titulo || !descripcion) {
        alert("Por favor, complete todos los campos requeridos.");
        return;
      }

      const formData = new FormData();
      formData.append("tipo", tipo);
      formData.append("titulo", titulo);
      formData.append("descripcion", descripcion);

      if (tipo === "producto") {
        formData.append("precio", precio);
        formData.append("estado", document.getElementById("estado_producto")?.value || "");
        formData.append("categoria", document.getElementById("categoria_producto")?.value || "");
      } else if (tipo === "mascota") {
        formData.append("edad", document.getElementById("edad")?.value || "");
        formData.append("raza", document.getElementById("raza")?.value || "");
        formData.append("tamano", document.getElementById("tamano")?.value || "");
        formData.append("vacunado", document.getElementById("vacunado")?.value || "");
        formData.append("desparasitado", document.getElementById("desparasitado")?.value || "");
      } else if (tipo === "servicio") {
        formData.append("duracion", document.getElementById("duracion")?.value || "");
        formData.append("lugar", document.getElementById("lugar")?.value || "");
        formData.append("telefono", document.getElementById("telefono")?.value || "");
        formData.append("precio", precio);
      }

      if (archivoSeleccionado) {
        formData.append("imagen", archivoSeleccionado);
      }

      try {
        const res = await fetch("crear_publicacion.php", {
          method: "POST",
          credentials: "include",
          body: formData
        });

        if (!res.ok) throw new Error("Error en la respuesta del servidor");

        const resultado = await res.json();
        if (resultado.success) {
          alert("Publicación creada con éxito");
          if (modalPublicar) modalPublicar.classList.add("oculto");
          resetFormulario();
          cargarPublicaciones();
        } else {
          alert("Error al crear publicación: " + (resultado.message || ""));
        }
      } catch (error) {
        console.error(error);
        alert("Error de conexión al crear publicación");
      }
    });
  }

  // Cargar publicaciones al cargar la página (por defecto "todo")
  cargarPublicaciones('todo');
});
