<?php
session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: index.php"); // Redirigir al inicio si no es administrador
    exit;
}

// Archivo: agregar_producto.php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $imagen = '';

    // Manejar la subida de la imagen
    if (isset($_FILES['imagenArchivo']) && $_FILES['imagenArchivo']['error'] === UPLOAD_ERR_OK) {
        $directorioSubida = 'imagenes/';
        $nombreArchivo = basename($_FILES['imagenArchivo']['name']);
        $rutaArchivo = $directorioSubida . $nombreArchivo;

        if (move_uploaded_file($_FILES['imagenArchivo']['tmp_name'], $rutaArchivo)) {
            $imagen = $rutaArchivo; // Guardar la ruta de la imagen
        } else {
            echo "<p>Error al subir la imagen.</p>";
        }
    } else {
        $imagen = $_POST['imagen'] ?? ''; // Usar la URL si no se sube un archivo

        // Validar si la URL de la imagen es accesible
        if (!empty($imagen) && !filter_var($imagen, FILTER_VALIDATE_URL)) {
            echo "<p>La URL de la imagen no es válida.</p>";
            exit;
        }

        // Verificar si la URL de la imagen es accesible
        if (!empty($imagen) && @getimagesize($imagen) === false) {
            echo "<p>No se puede acceder a la imagen desde la URL proporcionada.</p>";
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $precio, $imagen]);
        header("index.php"); 
    } catch (PDOException $e) {
        echo "<p>Error al agregar el producto: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
    <header>
        <h1><a href="index.php" style="text-decoration: none; color: inherit;">Agregar Producto</a></h1>
    </header>
    <main>
        <form method="POST" action="" enctype="multipart/form-data" class="formulario">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="input-text" placeholder="Nombre del producto" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" class="input-textarea" placeholder="Descripción del producto" required></textarea>

            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" class="input-text" step="0.01" placeholder="Precio del producto" required>

            <label for="imagen">Imagen (URL o archivo):</label>
            <input type="text" id="imagen" name="imagen" class="input-text" placeholder="Pegar URL">
            <input type="file" id="imagenArchivo" name="imagenArchivo" class="input-file" accept="image/*" onchange="optimizarImagen(event)">

            <button type="submit" class="btn-submit">Agregar Producto</button>
        </form>

        <script>
        function optimizarImagen(event) {
            const input = event.target;
            const file = input.files[0];

            if (file && file.size > 1024 * 1024) { // Limitar a 1MB
                alert('La imagen es demasiado grande. Por favor, elija una imagen de menos de 1MB.');
                input.value = '';
            }
        }
        </script>
    </main>
</body>
</html>