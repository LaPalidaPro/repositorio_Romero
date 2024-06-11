document.addEventListener("DOMContentLoaded", function () {
    fetch('/carrusel')
        .then(response => response.json())
        .then(images => {
            const carouselImagesContainer = document.getElementById('carousel-images');
            images.forEach((image, index) => {
                const div = document.createElement('div');
                div.className = `carousel-item${index === 0 ? ' active' : ''}`;
                div.innerHTML = `<img src="${image}" alt="Publicidad ${index + 1}" class="img-fluid">`;
                carouselImagesContainer.appendChild(div);
            });
        })
        .catch(error => console.error('Error fetching carousel images:', error));
});