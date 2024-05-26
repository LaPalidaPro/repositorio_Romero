document.addEventListener("DOMContentLoaded", function () {
  const audio = document.getElementById("audio");
  const playPauseBtn = document.getElementById("playPauseBtn");
  const seekSlider = document.getElementById("seekSlider");
  const volumeSlider = document.getElementById("volumeSlider");
  const volumeIcon = document.getElementById("volumeIcon");
  const currentTime = document.getElementById("currentTime");
  const totalTime = document.getElementById("totalTime");
  const heartIcon = document.getElementById("heart-icon");
  const toggleVisibilityBtn = document.querySelector(".toggle-visibility-btn");
  const toggleVisibilityIcon = toggleVisibilityBtn.querySelector("i");
  const btnBackward = document.getElementById("btnBackward");
  const btnForward = document.getElementById("btnForward");
  const songCards = document.querySelectorAll(".cardBtn .card");
  const songTitle = document.getElementById("songTitle");
  const artistName = document.getElementById("artistName");
  const enlaceDetallesCancion = document.getElementById("enlaceDetallesCancion");
  let currentSongId = null;

  if (
    !audio ||
    !playPauseBtn ||
    !seekSlider ||
    !volumeSlider ||
    !volumeIcon ||
    !currentTime ||
    !totalTime ||
    !heartIcon ||
    !toggleVisibilityBtn ||
    !toggleVisibilityIcon ||
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

  // Controlar la reproducción de audio
  playPauseBtn.addEventListener("click", function () {
    if (audio.paused) {
      audio.play().catch(error => {
        console.error("Error al intentar reproducir el audio:", error);
      });
      playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
    } else {
      audio.pause();
      playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
    }
  });

  // Retroceder en el audio
  btnBackward.addEventListener("click", function () {
    audio.currentTime -= 10; // Retrocede 10 segundos
    actualizarEnlaceDetallesCancion();
  });

  // Avanzar en el audio
  btnForward.addEventListener("click", function () {
    audio.currentTime += 10; // Avanza 10 segundos
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
    actualizarIconoVolumen();
    actualizarEnlaceDetallesCancion();
  });

  // Alternar la visibilidad de la barra de volumen
  volumeIcon.addEventListener("click", function () {
    alternarBarraSonido();
  });

  function alternarBarraSonido() {
    if (
      volumeSlider.style.display === "none" ||
      volumeSlider.style.display === ""
    ) {
      volumeSlider.style.display = "block";
    } else {
      volumeSlider.style.display = "none";
    }
  }

  function actualizarIconoVolumen() {
    const numeroLineas = Math.ceil(volumeSlider.value * 3);
    if (numeroLineas === 0) {
      volumeIcon.className = "fas fa-volume-mute";
    } else if (numeroLineas === 1) {
      volumeIcon.className = "fas fa-volume-down";
    } else {
      volumeIcon.className = "fas fa-volume-up";
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

  // Cambiar el icono del corazón
  heartIcon.addEventListener("click", function (event) {
    event.preventDefault();
    const icono = this.querySelector("i");
    if (icono.classList.contains("far")) {
      icono.classList.remove("far");
      icono.classList.add("fas", "text-custom");
    } else {
      icono.classList.remove("fas", "text-custom");
      icono.classList.add("far");
    }
    actualizarEnlaceDetallesCancion();
  });

  // Cerrar el reproductor
  const closeBtn = document.getElementById("closeBtn");
  if (closeBtn) {
    closeBtn.addEventListener("click", function () {
      audio.pause(); // Detener la reproducción
      audio.currentTime = 0; // Reiniciar el tiempo del audio
      const reproductor = document.querySelector(".reproductor");
      reproductor.style.display = "none"; // Ocultar el reproductor
    });
  }

  // Cambiar la canción al hacer clic en una carta
  songCards.forEach(function (card) {
    card.addEventListener("click", function () {
      const songSrc = card.getAttribute("data-audio-src");
      const songTitleText = card.querySelector(".card-title").textContent;
      const artistNameText = card.querySelector(".card-text").textContent;
      const songId = card.getAttribute("data-id");
      if (songSrc) {
        audio.src = songSrc;
        audio.play().catch(error => {
          console.error("Error al intentar reproducir el audio:", error);
        });
        playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';

        // Actualizar el título de la canción sin la extensión
        const cleanTitle = songTitleText.replace(/\.[^/.]+$/, "");
        songTitle.textContent = cleanTitle;

        // Actualizar el nombre del artista
        artistName.textContent = artistNameText;

        // Actualizar el ID de la canción actual
        currentSongId = songId;
        actualizarEnlaceDetallesCancion();

        // Asegurarse de mostrar el reproductor si está oculto
        const reproductorEmergente = document.querySelector(".reproductor");
        reproductorEmergente.style.display = "block";
      }
    });
  });

  function actualizarEnlaceDetallesCancion() {
    if (currentSongId) {
      const isHearted = heartIcon.querySelector("i").classList.contains("fas");
      enlaceDetallesCancion.href = `/harmonyhub/cancion/${currentSongId}?tiempo=${audio.currentTime}&volumen=${audio.volume}&corazon=${isHearted ? 1 : 0}`;
    }
  }

  // Redirigir a la vista de detalles de la canción
  enlaceDetallesCancion.addEventListener("click", function (event) {
    event.preventDefault();
    const isHearted = heartIcon.querySelector("i").classList.contains("fas");
    const currentTime = audio.currentTime;
    const volume = audio.volume;
    const corazon = isHearted ? 1 : 0;

    audio.pause(); // Detener la reproducción antes de redirigir

    window.location.href = `/harmonyhub/cancion/${currentSongId}?tiempo=${currentTime}&volumen=${volume}&corazon=${corazon}`;
  });
});
