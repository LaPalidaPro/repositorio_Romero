var audio = document.getElementById("audio");
var playPauseBtn = document.getElementById("playPauseBtn");
var seekSlider = document.getElementById("seekSlider");
var volumeSlider = document.getElementById("volumeSlider");
var currentTime = document.getElementById("currentTime");
var totalTime = document.getElementById("totalTime");

function abrirReproductor(cardElement) {
    var audioSource = cardElement.getAttribute('data-audio-src');
    var player = document.getElementById('audio');
    player.src = audioSource;
    player.play(); // Comienza la reproducción automáticamente

    // Muestra el reproductor si está oculto
    var reproductorEmergente = document.querySelector('.reproductor');
    reproductorEmergente.style.display = 'block';
}

function cambiarCancion(cardElement) {
    var audioSrc = cardElement.getAttribute('data-audio-src');

    // Crear una solicitud XMLHttpRequest
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/get-song-info?file=' + encodeURIComponent(audioSrc), true);
    xhr.onload = function() {
        if (this.status === 200) {
            var response = JSON.parse(this.responseText);
            var player = document.getElementById('audio');
            player.src = response.audioSrc; // URL del archivo de música
            player.load();
            player.play();

            // Actualizar metadatos del reproductor
            document.getElementById('tituloCancion').innerText = response.titulo;
            document.getElementById('nombreArtista').innerText = response.artista;

            // Asegurarse de mostrar el reproductor si está oculto
            var reproductorEmergente = document.querySelector('.reproductor');
            reproductorEmergente.style.display = 'block';
        }
    };
    xhr.onerror = function() {
        console.error('Error al cargar la canción.');
    };
    xhr.send();
}

function togglePlayPause(player) {
    if (player.paused) {
        player.play();
    } else {
        player.pause();
    }
}

