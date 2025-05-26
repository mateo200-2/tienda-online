<?php
session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: index.php"); // Redirigir al inicio si no es administrador
    exit;
}

include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $imagen = $_POST['imagen'] ?? '';

    try {
        $stmt = $pdo->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ? WHERE id = ?");
        $stmt->execute([$nombre, $descripcion, $precio, $imagen, $id]);
        echo "<p>Producto actualizado exitosamente.</p>";
    } catch (PDOException $e) {
        echo "<p>Error al actualizar el producto: " . $e->getMessage() . "</p>";
    }
} else {
    $id = $_GET['id'] ?? '';
    try {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch();
    } catch (PDOException $e) {
        echo "<p>Error al obtener el producto: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php" style="text-decoration: none; color: inherit;">Editar Producto</a></h1>
    </header>
    <main>
        <form method="POST" action="" class="formulario">
            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">

            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="input-text" value="<?php echo htmlspecialchars($producto['nombre']); ?>" placeholder="Nombre del producto" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" class="input-textarea" placeholder="Descripción del producto" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>

            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" class="input-text" step="0.01" value="<?php echo $producto['precio']; ?>" placeholder="Precio del producto" required>

            <label for="imagen">Imagen (URL):</label>
            <input type="text" id="imagen" name="imagen" class="input-text" value="<?php echo htmlspecialchars($producto['imagen']); ?>" placeholder="URL de la imagen">

            <button type="submit" class="btn-submit">Actualizar Producto</button>
        </form>
    </main>
</body>
</html>