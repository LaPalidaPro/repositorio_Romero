document.addEventListener("DOMContentLoaded", function() {
    const searchForm = document.getElementById('search-form');
    const searchInput = document.getElementById('search-input');
    const contenedorCanciones = document.getElementById('contenedorCanciones');

    if (searchForm && searchInput && contenedorCanciones) {
        searchForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const query = searchInput.value;

            fetch(`/buscador?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                    } else if (data.html) {
                        contenedorCanciones.innerHTML = data.html;
                        attachCardEventListeners();
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    }

    function attachCardEventListeners() {
        const cards = document.querySelectorAll(".cardBtn .card");
        cards.forEach(card => {
            card.addEventListener("click", function() {
                abrirReproductor(this);
            });
        });
    }

    attachCardEventListeners();
});

var audio = document.getElementById("audio");
var playPauseBtn = document.getElementById("playPauseBtn");
var seekSlider = document.getElementById("seekSlider");
var volumeSlider = document.getElementById("volumeSlider");
var currentTime = document.getElementById("currentTime");
var totalTime = document.getElementById("totalTime");

function abrirReproductor(cardElement) {
    cambiarCancion(cardElement);

    // Muestra el reproductor si está oculto
    var reproductorEmergente = document.querySelector('.reproductor');
    reproductorEmergente.style.display = 'block';
}

function cambiarCancion(cardElement) {
    var audioSrc = cardElement.getAttribute('data-audio-src');
    var titulo = cardElement.getAttribute('data-cancion');
    var artista = cardElement.getAttribute('data-artista');
    var favorito = cardElement.getAttribute('data-favorito') === 'true';

    var player = document.getElementById('audio');
    player.src = audioSrc;
    player.load();
    player.play();

    // Actualizar metadatos del reproductor
    document.getElementById('songTitle').innerText = titulo;
    document.getElementById('artistName').innerText = artista;

    // Actualizar icono de favorito
    actualizarIconoFavorito(favorito);

    // Actualizar el enlace de detalles de la canción
    actualizarEnlaceDetallesCancion(cardElement.getAttribute('data-id'), player.currentTime, player.volume, favorito);
}

function actualizarIconoFavorito(esFavorito) {
    var heartIcon = document.getElementById("heart-icon");
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
