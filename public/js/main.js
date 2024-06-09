document.addEventListener("DOMContentLoaded", function () {
  const searchForm = document.getElementById("search-form");
  const searchInput = document.getElementById("search-input");
  const contenedorCanciones = document.getElementById("contenedorCanciones");

  var audio = document.getElementById("audio");
  var playPauseBtn = document.getElementById("playPauseBtn");
  var seekSlider = document.getElementById("seekSlider");
  var volumeSlider = document.getElementById("volumeSlider");
  var currentTime = document.getElementById("currentTime");
  var totalTime = document.getElementById("totalTime");

  const playBtn = document.getElementById("playBtn");
  const pauseBtn = document.getElementById("pauseBtn");
  const volumenIcono = document.getElementById("volumenIcono");
  const heartIcon = document.getElementById("heart-icon");
  const btnBackward = document.getElementById("btnBackward");
  const btnForward = document.getElementById("btnForward");
  const songTitle = document.getElementById("songTitle");
  const artistName = document.getElementById("artistName");
  const enlaceDetallesCancion = document.getElementById("enlaceDetallesCancion");
  let currentSongId = null;

  if (!audio || !playBtn || !pauseBtn || !seekSlider || !volumeSlider || !volumenIcono || !currentTime || !totalTime || !heartIcon || !btnBackward || !btnForward || !songTitle || !artistName || !enlaceDetallesCancion) {
    console.error("Algunos elementos del DOM no fueron encontrados");
    return;
  }

  if (searchForm && searchInput && contenedorCanciones) {
    searchForm.addEventListener("submit", function (event) {
      event.preventDefault();
      const query = searchInput.value;

      fetch(`/buscador?query=${query}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.error) {
            alert(data.error);
          } else if (data.html) {
            contenedorCanciones.innerHTML = data.html;
            attachCardEventListeners();
          }
        })
        .catch((error) => console.error("Error:", error));
    });
  }

  function attachCardEventListeners() {
    const cards = document.querySelectorAll(".cardBtn .card");
    cards.forEach((card) => {
      card.addEventListener("click", function () {
        abrirReproductor(card);
      });
    });

    const prevBtn = document.querySelector('.btn-flecha-prev');
    const nextBtn = document.querySelector('.btn-flecha-next');

    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        const pagina = prevBtn.getAttribute('data-pagina');
        cargarPagina(pagina);
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        const pagina = nextBtn.getAttribute('data-pagina');
        cargarPagina(pagina);
      });
    }
  }

  attachCardEventListeners();

  function abrirReproductor(cardElement) {
    cambiarCancion(cardElement);
    var reproductorEmergente = document.querySelector(".reproductor");
    reproductorEmergente.style.display = "block";
  }

  function cambiarCancion(cardElement) {
    var audioSrc = cardElement.getAttribute("data-audio-src");
    var titulo = cardElement.getAttribute("data-cancion");
    var artista = cardElement.getAttribute("data-artista");
    var favorito = cardElement.getAttribute("data-favorito") === "true";
    const songId = cardElement.getAttribute("data-id");
    currentSongId = songId;
    var player = document.getElementById("audio");

    player.src = audioSrc;
    player.load();
    player.play();

    document.getElementById("songTitle").innerText = titulo;
    document.getElementById("artistName").innerText = artista;

    playBtn.style.display = "none";
    pauseBtn.style.display = "block";

    // Establecer el ID de la canción actual para incrementar reproducciones
    window.setCurrentSongId(songId);

    actualizarIconoFavorito(favorito);

    actualizarEnlaceDetallesCancion(
      cardElement.getAttribute("data-id"),
      player.currentTime,
      player.volume,
      favorito
    );
  }

  function actualizarIconoFavorito(esFavorito) {
    var icono = heartIcon.querySelector("i");
    if (esFavorito) {
      icono.classList.remove("far");
      icono.classList.add("fas", "text-custom");
    } else {
      icono.classList.remove("fas", "text-custom");
      icono.classList.add("far");
    }
  }

  function actualizarEnlaceDetallesCancion(id, tiempo, volumen, corazon) {
    var enlaceDetallesCancion = document.getElementById("enlaceDetallesCancion");
    enlaceDetallesCancion.href = `/harmonyhub/cancion/${id}?tiempo=${tiempo}&volumen=${volumen}&corazon=${corazon ? 1 : 0}`;
  }

  function togglePlayPause(player) {
    if (player.paused) {
      player.play();
    } else {
      player.pause();
    }
  }

  const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute("content") : null;

  if (!csrfToken) {
    console.error("Token CSRF no encontrado");
    return;
  }

  audio.currentTime = parseFloat(audio.dataset.tiempo) || 0;
  audio.volume = parseFloat(audio.dataset.volumen) || 1;
  actualizarIconoVolumen(audio.volume);
  volumeSlider.value = audio.volume;

  actualizarIconoFavorito(parseInt(audio.dataset.corazon) === 1);

  playBtn.addEventListener("click", function () {
    audio
      .play()
      .then(() => {
        playBtn.style.display = "none";
        pauseBtn.style.display = "block";
      })
      .catch((error) => {
        console.error("Error al intentar reproducir el audio:", error);
      });
  });

  pauseBtn.addEventListener("click", function () {
    audio.pause();
    pauseBtn.style.display = "none";
    playBtn.style.display = "block";
  });

  btnBackward.addEventListener("click", function () {
    audio.currentTime -= 10;
    actualizarEnlaceDetallesCancion();
  });

  btnForward.addEventListener("click", function () {
    audio.currentTime += 10;
    actualizarEnlaceDetallesCancion();
  });

  seekSlider.addEventListener("input", function () {
    const seekTo = audio.duration * (seekSlider.value / 100);
    audio.currentTime = seekTo;
    actualizarEnlaceDetallesCancion();
  });

  volumeSlider.addEventListener("input", function () {
    audio.volume = volumeSlider.value;
    actualizarIconoVolumen(audio.volume);
    actualizarEnlaceDetallesCancion();
  });

  volumenIcono.addEventListener("click", function () {
    alternarBarraSonido();
  });

  function alternarBarraSonido() {
    if (volumeSlider.style.display === "none") {
      volumeSlider.style.display = "block";
    } else {
      volumeSlider.style.display = "none";
    }
  }

  function actualizarIconoVolumen(volumen) {
    if (volumen === 0) {
      volumenIcono.className = "fas fa-volume-mute";
    } else if (volumen <= 0.5) {
      volumenIcono.className = "fas fa-volume-down";
    } else {
      volumenIcono.className = "fas fa-volume-up";
    }
  }

  audio.ontimeupdate = function () {
    const currentMins = Math.floor(audio.currentTime / 60);
    const currentSecs = Math.floor(audio.currentTime % 60);
    const durationMins = Math.floor(audio.duration / 60);
    const durationSecs = Math.floor(audio.duration % 60);
    currentTime.innerHTML = `${currentMins}:${currentSecs < 10 ? "0" : ""}${currentSecs}`;
    totalTime.innerHTML = `${durationMins}:${durationSecs < 10 ? "0" : ""}${durationSecs}`;
    seekSlider.value = (audio.currentTime / audio.duration) * 100;
    actualizarEnlaceDetallesCancion();
  };

  function actualizarIconoFavorito(esFavorito) {
    const icono = heartIcon.querySelector("i");
    if (esFavorito) {
      icono.classList.remove("far");
      icono.classList.add("fas", "text-custom");
    } else {
      icono.classList.remove("fas", "text-custom");
      icono.classList.add("far");
    }
  }

  heartIcon.addEventListener("click", function (event) {
    event.preventDefault();

    if (heartIcon.getAttribute("data-clicked") === "true") {
      return;
    }
    heartIcon.setAttribute("data-clicked", "true");

    toggleFavorito(currentSongId)
      .then((response) => {
        heartIcon.setAttribute("data-clicked", "false");
        if (response.status === "added") {
          actualizarIconoFavorito(true);
          actualizarIconoFavoritoEnTodasLasInstancias(currentSongId, true);
          mostrarMensaje("Canción añadida a tu lista de favoritos", heartIcon);
        } else if (response.status === "removed") {
          actualizarIconoFavorito(false);
          actualizarIconoFavoritoEnTodasLasInstancias(currentSongId, false);
          mostrarMensaje("Canción eliminada de tu lista de favoritos", heartIcon);
        } else if (response.status === "error" && response.redirect) {
          window.location.href = response.redirect;
        }
        actualizarEnlaceDetallesCancion();
      })
      .catch((error) => {
        heartIcon.setAttribute("data-clicked", "false");
        console.error("Error al cambiar el estado de favorito:", error);
      });
  });

  function actualizarIconoFavoritoEnTodasLasInstancias(songId, esFavorito) {
    const actualSongCards = document.querySelectorAll(".cardBtn .card");
    for (const card of actualSongCards) {
      if (card.getAttribute("data-id") !== songId) {
        continue;
      }

      card.setAttribute("data-favorito", esFavorito ? "true" : "false");
      const iconoCard = card.querySelector(".fa-heart");
      if (!iconoCard) {
        continue;
      }

      if (esFavorito) {
        iconoCard.classList.remove("far");
        iconoCard.classList.add("fas", "text-custom");
      } else {
        iconoCard.classList.remove("fas", "text-custom");
        iconoCard.classList.add("far");
      }
    }
  }

  function toggleFavorito(cancionId) {
    return fetch(`/favoritos/toggle/${cancionId}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-CSRF-Token": csrfToken,
      },
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then((data) => {
        return data;
      })
      .catch((error) => {
        console.error("Error al intentar cambiar el estado de favorito:", error);
        return { status: "error" };
      });
  }

  function mostrarMensaje(mensaje, elemento) {
    const mensajeElemento = document.createElement("div");
    mensajeElemento.className = "mensaje-emergente";
    mensajeElemento.innerText = mensaje;

    const rect = elemento.getBoundingClientRect();
    mensajeElemento.style.position = "absolute";
    mensajeElemento.style.left = `${rect.left + window.scrollX}px`;
    mensajeElemento.style.top = `${rect.top + window.scrollY - 30}px`;

    document.body.appendChild(mensajeElemento);

    setTimeout(() => {
      mensajeElemento.remove();
    }, 3000);
  }

  const closeBtn = document.getElementById("closeBtn");
  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      audio.pause();
      audio.currentTime = 0;
      const reproductor = document.querySelector(".reproductor");
      reproductor.style.display = "none";
    });
  }

  function actualizarEnlaceDetallesCancion() {
    if (currentSongId) {
      const isHearted = heartIcon.querySelector("i").classList.contains("fas");
      enlaceDetallesCancion.href = `/harmonyhub/cancion/${currentSongId}?tiempo=${audio.currentTime}&volumen=${audio.volume}&corazon=${isHearted ? 1 : 0}`;
    }
  }

  enlaceDetallesCancion.addEventListener("click", function (event) {
    event.preventDefault();
    const isHearted = heartIcon.querySelector("i").classList.contains("fas");
    const currentTime = audio.currentTime;
    const volume = audio.volume;
    const corazon = isHearted ? 1 : 0;

    audio.pause();
    window.location.href = `/harmonyhub/cancion/${currentSongId}?tiempo=${currentTime}&volumen=${volume}&corazon=${corazon}`;
  });

  function cargarPagina(pagina) {
    fetch(`/harmonyhub/cargar-canciones?pagina=${pagina}`)
      .then((response) => response.json())
      .then((data) => {
        document.getElementById("contenedorCanciones").innerHTML = data.html;
        attachCardEventListeners(); // Re-adjuntar eventos a las nuevas cartas
      })
      .catch((error) => console.log("Error:", error));
  }

  // Adjuntar eventos a los botones de paginación
  document.addEventListener('click', function (event) {
    if (event.target.classList.contains('btn-flecha-prev')) {
      const pagina = event.target.getAttribute('data-pagina');
      cargarPagina(pagina);
    }
    if (event.target.classList.contains('btn-flecha-next')) {
      const pagina = event.target.getAttribute('data-pagina');
      cargarPagina(pagina);
    }
  });

  attachCardEventListeners();
});