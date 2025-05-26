// Archivo: script.js

function optimizarImagen(event) {
    const input = event.target;
    const file = input.files[0];

    if (file && file.size > 1024 * 1024) { // Limitar a 1MB
        alert('La imagen es demasiado grande. Por favor, elija una imagen de menos de 1MB.');
        input.value = '';
    }
}