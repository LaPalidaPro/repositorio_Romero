document.addEventListener('DOMContentLoaded', function () {
    const elementIds = ["audio", "playButton", "pauseButton", "seekSlider", "currentTime", "totalTime", "btnBackward", "btnForward", "heart-icon-empty", "heart-icon-full", "volumeIcon", "volumeSlider"];
    const elements = {};

    // Asignar elementos del DOM al objeto `elements`
    for (const id of elementIds) {
        elements[id] = document.getElementById(id);
        if (!elements[id]) {
            console.error(`Elemento con id "${id}" no fue encontrado.`);
            return;
        }
    }

    console.log("Todos los elementos del DOM fueron encontrados.");

    const audio = elements["audio"];
    const playBtn = elements["playButton"];
    const pauseBtn = elements["pauseButton"];
    const seekSlider = elements["seekSlider"];
    const currentTimeElem = elements["currentTime"];
    const totalTimeElem = elements["totalTime"];
    const btnBackward = elements["btnBackward"];
    const btnForward = elements["btnForward"];
    const heartIconEmpty = elements["heart-icon-empty"];
    const heartIconFull = elements["heart-icon-full"];
    const volumeIcon = elements["volumeIcon"];
    const volumeSlider = elements["volumeSlider"];

    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute('content') : null;

    if (!csrfToken) {
        console.error("Token CSRF no encontrado");
        return;
    }

    // Función para actualizar el icono del volumen
    function actualizarIconoVolumen(volumen) {
        if (volumen === 0) {
            volumeIcon.className = "fas fa-volume-mute me-2";
        } else if (volumen <= 0.5) {
            volumeIcon.className = "fas fa-volume-down me-2";
        } else {
            volumeIcon.className = "fas fa-volume-up me-2";
        }
    }

    // Establecer los valores iniciales
    audio.currentTime = parseFloat(audio.dataset.tiempo) || 0;
    audio.volume = parseFloat(audio.dataset.volumen) || 1;
    actualizarIconoVolumen(audio.volume);
    volumeSlider.value = audio.volume;
    if (parseInt(audio.dataset.corazon) === 1) {
        heartIconEmpty.style.display = "none";
        heartIconFull.style.display = "block";
    }

    let firstPlay = true;

    // Reproducir automáticamente al cargar
    if (firstPlay) {
        audio.play().then(() => {
            console.log("Reproducción automática iniciada.");
            playBtn.style.display = "none";
            pauseBtn.style.display = "block";
            firstPlay = false;
        }).catch(error => {
            console.error("Error al intentar reproducir el audio automáticamente:", error);
        });
    }

    const playClick = () => {
            audio.play().then(() => {
            console.log("Audio reproducido.");
            playBtn.style.display = "none";
            pauseBtn.style.display = "block";
            }).catch(error => {
                console.error("Error al intentar reproducir el audio:", error);
            });
    };

    const pauseClick = () => {
            audio.pause();
        pauseBtn.style.display = "none";
        playBtn.style.display = "block";
    };

    playBtn.addEventListener('click', playClick);
    pauseBtn.addEventListener('click', pauseClick);

    // Retroceder en el audio
    btnBackward.addEventListener('click', function () {
        audio.currentTime -= 10; // Retrocede 10 segundos
        console.log("Retroceder 10 segundos. Tiempo actual:", audio.currentTime);
    });

    // Avanzar en el audio
    btnForward.addEventListener('click', function () {
        audio.currentTime += 10; // Avanza 10 segundos
        console.log("Avanzar 10 segundos. Tiempo actual:", audio.currentTime);
    });

    // Buscar en el audio
    seekSlider.addEventListener('input', function () {
        const seekTo = audio.duration * (seekSlider.value / 100);
        audio.currentTime = seekTo;
        console.log("Buscar en el audio. Tiempo actual:", audio.currentTime);
    });

    // Actualizar el tiempo de reproducción
    audio.ontimeupdate = function () {
        const currentMins = Math.floor(audio.currentTime / 60);
        const currentSecs = Math.floor(audio.currentTime % 60);
        currentTimeElem.innerHTML = `${currentMins}:${currentSecs < 10 ? '0' : ''}${currentSecs}`;
        seekSlider.value = (audio.currentTime / audio.duration) * 100;
    };

    // Ajustar el tiempo total cuando los metadatos se cargan
    audio.onloadedmetadata = function () {
        const totalMins = Math.floor(audio.duration / 60);
        const totalSecs = Math.floor(audio.duration % 60);
        totalTimeElem.innerHTML = `${totalMins}:${totalSecs < 10 ? '0' : ''}${totalSecs}`;
    };

    // Manejo de errores en la reproducción
    audio.onerror = function (e) {
        console.error("Error al intentar reproducir el audio:", e);
    };

    // Función para mostrar mensaje emergente
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

    // Función para alternar favorito
    function toggleFavorito(cancionId) {
        return fetch(`/favoritos/toggle/${cancionId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            return data;
        })
        .catch(error => {
            console.error('Error al intentar cambiar el estado de favorito:', error);
            return { status: 'error' };
        });
    }

    // Cambiar el icono del corazón
    heartIconEmpty.addEventListener("click", function (event) {
        event.preventDefault();
        heartIconEmpty.style.display = "none";
        heartIconFull.style.display = "block";
        console.log("Corazón lleno activado.");
    });

    heartIconFull.addEventListener("click", function (event) {
        event.preventDefault();
        heartIconFull.style.display = "none";
        heartIconEmpty.style.display = "block";
        console.log("Corazón vacío activado.");
    });
    // visibilidad el volumen y el icono del volumen
    volumeIcon.addEventListener("click", function (event) {
        event.preventDefault();
        if (volumeSlider.style.display === "none") {
            volumeSlider.style.display = "block";
        } else {
            volumeSlider.style.display = "none";
        }
    });

    // Cambiar el volumen y el icono del volumen
    volumeSlider.addEventListener("input", function () {
        audio.volume = parseFloat(volumeSlider.value);
        actualizarIconoVolumen(audio.volume);
        console.log("Volumen cambiado a:", audio.volume);
    });
});
