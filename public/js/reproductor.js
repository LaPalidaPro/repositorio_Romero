document.addEventListener("DOMContentLoaded", function () {
  const audio = document.getElementById("audio");
  const playBtn = document.getElementById("playBtn");
  const pauseBtn = document.getElementById("pauseBtn");
  const seekSlider = document.getElementById("seekSlider");
  const volumeSlider = document.getElementById("volumeSlider");
  const volumenIcono = document.getElementById("volumenIcono");
  const currentTime = document.getElementById("currentTime");
  const totalTime = document.getElementById("totalTime");
  const heartIcon = document.getElementById("heart-icon");
  const btnBackward = document.getElementById("btnBackward");
  const btnForward = document.getElementById("btnForward");
  const songCards = document.querySelectorAll(".cardBtn .card");
  const songTitle = document.getElementById("songTitle");
  const artistName = document.getElementById("artistName");
  const enlaceDetallesCancion = document.getElementById("enlaceDetallesCancion");
  let currentSongId = null;

  if (
    !audio ||
    !playBtn ||
    !pauseBtn ||
    !seekSlider ||
    !volumeSlider ||
    !volumenIcono ||
    !currentTime ||
    !totalTime ||
    !heartIcon ||
    !btnBackward ||
    !btnForward ||
    !songCards.length ||
    !songTitle ||
    !artistName ||
    !enlaceDetallesCancion
  ) {
    console.error("Algunos elementos del DOM no fueron encontrados");
    return;
  }

  const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute("content") : null;

  if (!csrfToken) {
    console.error("Token CSRF no encontrado");
    return;
  }

  // Establecer los valores iniciales
  audio.currentTime = parseFloat(audio.dataset.tiempo) || 0;
  audio.volume = parseFloat(audio.dataset.volumen) || 1;
  actualizarIconoVolumen(audio.volume);
  volumeSlider.value = audio.volume;

  actualizarIconoFavorito(parseInt(audio.dataset.corazon) === 1);

  // Funciones para manejar el botón de play/pause
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

  // Retroceder en el audio
  btnBackward.addEventListener("click", function () {
    audio.currentTime -= 10;
    actualizarEnlaceDetallesCancion();
  });

  // Avanzar en el audio
  btnForward.addEventListener("click", function () {
    audio.currentTime += 10;
    actualizarEnlaceDetallesCancion();
  });

  // Buscar en el audio
  seekSlider.addEventListener("input", function () {
    const seekTo = audio.duration * (seekSlider.value / 100);
    audio.currentTime = seekTo;
    actualizarEnlaceDetallesCancion();
  });

  // Ajustar volumen
  volumeSlider.addEventListener("input", function () {
    audio.volume = volumeSlider.value;
    actualizarIconoVolumen(audio.volume);
    actualizarEnlaceDetallesCancion();
  });

  // Alternar la visibilidad de la barra de volumen
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

  // Actualizar el tiempo de reproducción
  audio.ontimeupdate = function () {
    const currentMins = Math.floor(audio.currentTime / 60);
    const currentSecs = Math.floor(audio.currentTime % 60);
    const durationMins = Math.floor(audio.duration / 60);
    const durationSecs = Math.floor(audio.duration % 60);
    currentTime.innerHTML = `${currentMins}:${
      currentSecs < 10 ? "0" : ""
    }${currentSecs}`;
    totalTime.innerHTML = `${durationMins}:${
      durationSecs < 10 ? "0" : ""
    }${durationSecs}`;
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

  // Cambiar el icono del corazón y manejar el estado de favorito
  heartIcon.addEventListener("click", function (event) {
    event.preventDefault();
    if (heartIcon.getAttribute("data-clicked") === "true") {
      return; // Salir si ya se ha hecho clic recientemente
    }
    heartIcon.setAttribute("data-clicked", "true"); // Marcar como clicado

    toggleFavorito(currentSongId)
      .then(response => {
        heartIcon.setAttribute("data-clicked", "false"); // Desmarcar después de la respuesta
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
      .catch(error => {
        heartIcon.setAttribute("data-clicked", "false"); // Desmarcar en caso de error
        console.error("Error al cambiar el estado de favorito:", error);
      });
  });

  function actualizarIconoFavoritoEnTodasLasInstancias(songId, esFavorito) {
    songCards.forEach(card => {
      if (card.getAttribute("data-id") === songId) {
        card.setAttribute("data-favorito", esFavorito ? "true" : "false");
        const iconoCard = card.querySelector(".fa-heart");
        if (iconoCard) {
          if (esFavorito) {
            iconoCard.classList.remove("far");
            iconoCard.classList.add("fas", "text-custom");
          } else {
            iconoCard.classList.remove("fas", "text-custom");
            iconoCard.classList.add("far");
          }
        }
      }
    });
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
      .then(response => {
        if (!response.ok) {
          throw new Error("Network response was not ok");
        }
        return response.json();
      })
      .then(data => {
        return data;
      })
      .catch(error => {
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

  // Cerrar el reproductor
  const closeBtn = document.getElementById("closeBtn");
  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      audio.pause();
      audio.currentTime = 0;
      const reproductor = document.querySelector(".reproductor");
      reproductor.style.display = "none";
    });
  }

  // Cambiar la canción al hacer clic en una carta
  songCards.forEach(function (card) {
    card.addEventListener("click", function () {
      const songSrc = card.getAttribute("data-audio-src");
      const songTitleText = card.querySelector(".card-title").textContent;
      const artistNameText = card.querySelector(".card-text").textContent;
      const songId = card.getAttribute("data-id");
      const esFavorito = card.getAttribute("data-favorito") === "true";

      if (songSrc) {
        audio.src = songSrc;
        audio.play().then(() => {
          playBtn.style.display = "none";
          pauseBtn.style.display = "block";
        }).catch(error => {
          console.error("Error al intentar reproducir el audio:", error);
        });

        const cleanTitle = songTitleText.replace(/\.[^/.]+$/, "");
        songTitle.textContent = cleanTitle;
        artistName.textContent = artistNameText;
        currentSongId = songId;

        actualizarIconoFavorito(esFavorito);

        actualizarEnlaceDetallesCancion();
        const reproductorEmergente = document.querySelector(".reproductor");
        reproductorEmergente.style.display = "block";
      }
    });
  });

  function actualizarEnlaceDetallesCancion() {
    if (currentSongId) {
      const isHearted = heartIcon.querySelector("i").classList.contains("fas");
      enlaceDetallesCancion.href = `/harmonyhub/cancion/${currentSongId}?tiempo=${
        audio.currentTime
      }&volumen=${audio.volume}&corazon=${isHearted ? 1 : 0}`;
    }
  }

  // Redirigir a la vista de detalles de la canción
  enlaceDetallesCancion.addEventListener("click", function (event) {
    event.preventDefault();
    const isHearted = heartIcon.querySelector("i").classList.contains("fas");
    const currentTime = audio.currentTime;
    const volume = audio.volume;
    const corazon = isHearted ? 1 : 0;

    audio.pause();
    window.location.href = `/harmonyhub/cancion/${currentSongId}?tiempo=${currentTime}&volumen=${volume}&corazon=${corazon}`;
  });
});
