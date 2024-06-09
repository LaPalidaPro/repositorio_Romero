document.addEventListener("DOMContentLoaded", function () {
    var audio = document.getElementById("audio");
    const csrfTokenElement = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenElement ? csrfTokenElement.getAttribute("content") : null;
    let currentSongId = null;

    if (!audio) {
        console.error("Elemento de audio no encontrado");
        return;
    }

    function incrementarReproducciones(cancionId) {
        fetch(`/incrementar-reproducciones/${cancionId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log(`Número de reproducciones actualizado: ${data.numeroReproducciones}`);
            } else {
                console.error('Error al actualizar el número de reproducciones');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    audio.addEventListener("play", function () {
        if (currentSongId) {
            incrementarReproducciones(currentSongId);
        }
    });

    window.setCurrentSongId = function(songId) {
        currentSongId = songId;
    };
});
