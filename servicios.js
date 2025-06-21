document.addEventListener("DOMContentLoaded", () => {
  const serviciosContainer = document.getElementById("servicios-lista-container");
  const searchInput = document.getElementById("search-input");

  // Función para obtener servicios desde PHP
  async function cargarServicios(busqueda = '') {
    try {
      const url = `get_servicios.php?search=${encodeURIComponent(busqueda)}`;
      const response = await fetch(url);
      const data = await response.json();

      if (data.success) {
        mostrarServicios(data.servicios);
      } else {
        serviciosContainer.innerHTML = "<p>No se pudieron cargar los servicios.</p>";
      }
    } catch (error) {
      serviciosContainer.innerHTML = "<p>Error al cargar los servicios.</p>";
      console.error(error);
    }
  }

  // Función para renderizar servicios en el contenedor
  function mostrarServicios(servicios) {
    if (servicios.length === 0) {
      serviciosContainer.innerHTML = "<p>No se encontraron servicios.</p>";
      return;
    }

    serviciosContainer.innerHTML = servicios.map(servicio => `
      <article class="servicio-card" style="border:1px solid #ddd; border-radius:8px; padding:1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
        <img src="${servicio.imagen}" alt="${servicio.nombre}" style="width:100%; height:160px; object-fit:cover; border-radius:6px 6px 0 0;" />
        <h3 style="margin-top:1rem; font-size:1.25rem; color:#10b981;">${servicio.nombre}</h3>
        <p style="color:#555; margin:0.5rem 0;">${servicio.descripcion}</p>
        <p style="font-weight:700; color:#111827;">Precio: $${parseFloat(servicio.precio).toFixed(2)}</p>
        <p style="font-size:0.85rem; color:#888;">Publicado por: ${servicio.usuario_nombre}</p>
      </article>
    `).join('');
  }

  // Buscar mientras se escribe (con pequeño debounce)
  let debounceTimer;
  searchInput.addEventListener("input", () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      cargarServicios(searchInput.value.trim());
    }, 300);
  });

  // Carga inicial
  cargarServicios();
});
