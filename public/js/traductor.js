document.addEventListener("DOMContentLoaded", function () {
    const changeLocaleLinks = document.querySelectorAll(".change-locale");

    changeLocaleLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            const locale = link.getAttribute("data-locale");
            fetch(`/harmonyhub/cambioIdioma/${locale}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload();
                } else {
                    console.error("Error changing locale");
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });
});