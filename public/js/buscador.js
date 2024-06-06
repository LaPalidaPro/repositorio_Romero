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