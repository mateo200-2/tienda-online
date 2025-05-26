<?php
session_start();
require_once 'conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Obtener productos de la base de datos
$sql = "SELECT * FROM productos";
$resultado = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Productos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container-admin">
        <h2>Gestión de Productos</h2>
        <a href="index.php" class="btn-volver">Volver al inicio</a>
        <table class="tabla-admin">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Imagen</th>
                    <th>Categoría</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($producto = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $producto['id'] ?></td>
                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                    <td>$<?= number_format($producto['precio'], 2) ?></td>
                    <td><img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="Imagen" class="img-tabla"></td>
                    <td><?= htmlspecialchars($producto['categoria']) ?></td>
                    <td>
                        <a href="editar_producto.php?id=<?= $producto['id'] ?>" class="btn-editar">Editar</a>
                        <a href="eliminar_producto.php?id=<?= $producto['id'] ?>" class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
