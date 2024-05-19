document.addEventListener('DOMContentLoaded', function() {
    const audio = document.getElementById("audio");
    const playPauseBtn = document.getElementById("playPauseBtn");
    const seekSlider = document.getElementById("seekSlider");
    const volumeSlider = document.getElementById("volumeSlider");
    const volumeIcon = document.getElementById("volumeIcon");
    const currentTime = document.getElementById("currentTime");
    const totalTime = document.getElementById("totalTime");
    const heartIcon = document.getElementById("heart-icon");
    const toggleVisibilityBtn = document.querySelector('.toggle-visibility-btn');
    const toggleVisibilityIcon = toggleVisibilityBtn.querySelector('i');
    const btnBackward = document.getElementById("btnBackward");
    const btnForward = document.getElementById("btnForward");
    const songCards = document.querySelectorAll('.cardBtn .card');
    const songTitle = document.getElementById("songTitle");
    const artistName = document.getElementById("artistName");

    if (!audio || !playPauseBtn || !seekSlider || !volumeSlider || !volumeIcon || !currentTime || !totalTime || !heartIcon || !toggleVisibilityBtn || !toggleVisibilityIcon || !btnBackward || !btnForward || !songCards.length || !songTitle || !artistName) {
        console.error("Algunos elementos del DOM no fueron encontrados");
        return;
    }

    // Controlar la reproducción de audio
    playPauseBtn.addEventListener('click', function() {
        if (audio.paused) {
            audio.play();
            playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
        } else {
            audio.pause();
            playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
        }
    });

    // Retroceder en el audio
    btnBackward.addEventListener('click', function() {
        audio.currentTime -= 10; // Retrocede 10 segundos
    });

    // Avanzar en el audio
    btnForward.addEventListener('click', function() {
        audio.currentTime += 10; // Avanza 10 segundos
    });

    // Buscar en el audio
    seekSlider.addEventListener('input', function() {
        const seekTo = audio.duration * (seekSlider.value / 100);
        audio.currentTime = seekTo;
    });

    // Ajustar volumen
    volumeSlider.addEventListener('input', function() {
        audio.volume = volumeSlider.value;
        actualizarIconoVolumen();
    });

    // Alternar la visibilidad de la barra de volumen
    volumeIcon.addEventListener('click', function() {
        alternarBarraSonido();
    });

    function alternarBarraSonido() {
        if (volumeSlider.style.display === 'none' || volumeSlider.style.display === '') {
            volumeSlider.style.display = 'block';
        } else {
            volumeSlider.style.display = 'none';
        }
    }

    function actualizarIconoVolumen() {
        const numeroLineas = Math.ceil(volumeSlider.value * 3);
        if (numeroLineas === 0) {
            volumeIcon.className = 'fas fa-volume-mute';
        } else if (numeroLineas === 1) {
            volumeIcon.className = 'fas fa-volume-down';
        } else {
            volumeIcon.className = 'fas fa-volume-up';
        }
    }

    // Actualizar el tiempo de reproducción
    audio.ontimeupdate = function() {
        const currentMins = Math.floor(audio.currentTime / 60);
        const currentSecs = Math.floor(audio.currentTime % 60);
        const durationMins = Math.floor(audio.duration / 60);
        const durationSecs = Math.floor(audio.duration % 60);
        currentTime.innerHTML = `${currentMins}:${currentSecs < 10 ? '0' : ''}${currentSecs}`;
        totalTime.innerHTML = `${durationMins}:${durationSecs < 10 ? '0' : ''}${durationSecs}`;
        seekSlider.value = (audio.currentTime / audio.duration) * 100;
    };

    // Cambiar el icono del corazón
    heartIcon.addEventListener("click", function(event) {
        event.preventDefault();
        const icono = this.querySelector("i");
        if (icono.classList.contains("far")) {
            icono.classList.remove("far");
            icono.classList.add("fas", "text-custom");
        } else {
            icono.classList.remove("fas", "text-custom");
            icono.classList.add("far");
        }
    });

    // Minimizar el reproductor
    if (toggleVisibilityBtn) {
        toggleVisibilityBtn.addEventListener('click', minimizarReproductor);
    }

    function minimizarReproductor() {
        const reproductor = document.querySelector('.reproductor');
        if (reproductor) {
            reproductor.classList.toggle('minimized');
            if (reproductor.classList.contains('minimized')) {
                toggleVisibilityIcon.classList.remove('fa-eye');
                toggleVisibilityIcon.classList.add('fa-eye-slash');
            } else {
                toggleVisibilityIcon.classList.remove('fa-eye-slash');
                toggleVisibilityIcon.classList.add('fa-eye');
            }
        } else {
            console.error("Elemento del reproductor no encontrado");
        }
    }

    // Cambiar la canción al hacer clic en una carta
    songCards.forEach(function(card) {
        card.addEventListener('click', function() {
            const songSrc = card.getAttribute('data-audio-src');
            const songTitleText = card.getAttribute('data-cancion');
            const artistNameText = card.getAttribute('data-artista');
            if (songSrc) {
                audio.src = songSrc;
                audio.play();
                playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';

                // Actualizar el título de la canción y el nombre del artista
                const cleanTitle = songTitleText.replace(/\.[^/.]+$/, "");
                songTitle.textContent = cleanTitle;
                artistName.textContent = artistNameText;

                // Asegurarse de mostrar el reproductor si está oculto
                const reproductorEmergente = document.querySelector('.reproductor');
                reproductorEmergente.style.display = 'block';
            }
        });
    });
});
