
  // Variables para controlar la paginación
  let paginaActual = 1;
  const cancionesPorPagina = 8;
  const totalCanciones = canciones.length;

  // Función para mostrar las canciones en la página actual
  function mostrarCanciones(pagina) {
    const indiceInicio = (pagina - 1) * cancionesPorPagina;
    const indiceFin = indiceInicio + cancionesPorPagina;
    const cancionesAMostrar = canciones.slice(indiceInicio, indiceFin);

    const contenedorCanciones = document.getElementById('contenedorCanciones');
    contenedorCanciones.innerHTML = '';

    cancionesAMostrar.forEach(cancion => {
      const tarjeta = `
    <div class="col-md-3">
      <div class="cardBtn">
        <a href="#">
          <div class="card">
            <img src="https://via.placeholder.com/300" class="card-img-top" alt="Canción">
            <div class="card-body">
              <h5 class="card-title">${cancion.titulo}</h5>
              <p class="card-text">${cancion.artista}</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  `;
      contenedorCanciones.innerHTML += tarjeta;
    });
  }

  // Función para manejar el evento de clic en el botón Siguiente
  document.getElementById('btnSiguiente').addEventListener('click', function () {
    if (paginaActual < Math.ceil(totalCanciones / cancionesPorPagina)) {
      paginaActual++;
      mostrarCanciones(paginaActual);
      document.getElementById('btnAnterior').removeAttribute('disabled');
      if (paginaActual === Math.ceil(totalCanciones / cancionesPorPagina)) {
        this.setAttribute('disabled', true);
      }
    }
  });

  // Función para manejar el evento de clic en el botón Anterior
  document.getElementById('btnAnterior').addEventListener('click', function () {
    if (paginaActual > 1) {
      paginaActual--;
      mostrarCanciones(paginaActual);
      document.getElementById('btnSiguiente').removeAttribute('disabled');
      if (paginaActual === 1) {
        this.setAttribute('disabled', true);
      }
    }
  });

  // Mostrar las primeras canciones al cargar la página
  mostrarCanciones(paginaActual);
  document.getElementById('btnAnterior').setAttribute('disabled', true);
  // Reproductor de música
  const btnPlayPause = document.getElementById('btnPlayPause');
  const btnAnterior = document.getElementById('btnAnterior');
  const btnSiguiente = document.getElementById('btnSiguiente');
  const volumen = document.getElementById('volumen');
  const progreso = document.getElementById('progreso');

  // Eventos para los controles de reproducción
  btnPlayPause.addEventListener('click', () => {
    // Toggle play/pause
  });

  btnAnterior.addEventListener('click', () => {
    // Reproducir pista anterior
  });

  btnSiguiente.addEventListener('click', () => {
    // Reproducir pista siguiente
  });

  volumen.addEventListener('input', () => {
    // Ajustar volumen
  });

  progreso.addEventListener('input', () => {
    // Cambiar posición de reproducción
  });

  // Barra de búsqueda mejorada
  const inputBusqueda = document.getElementById('inputBusqueda');
  const resultadosBusqueda = document.getElementById('resultadosBusqueda');

  inputBusqueda.addEventListener('input', () => {
    // Realizar búsqueda y mostrar resultados de autocompletado
  });





