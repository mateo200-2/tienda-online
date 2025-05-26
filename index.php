<?php
session_start();
// Archivo: index.php
include 'conexion.php';

try {
    // Consulta para obtener los productos
    $stmt = $pdo->query("SELECT * FROM productos");
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener los productos: " . $e->getMessage());
}

$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $producto_id = $_POST['producto_id'];
    $cantidad = 1; // Por defecto, se agrega 1 unidad
    $usuario_id = $_SESSION['usuario_id'] ?? null; // Obtener el usuario_id de la sesión

    if ($usuario_id) {
        try {
            $stmt = $pdo->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE cantidad = cantidad + 1");
            $stmt->execute([$usuario_id, $producto_id, $cantidad]);
            $mensaje = "<p>Producto agregado al carrito.</p>";
        } catch (PDOException $e) {
            $mensaje = "<p>Error al agregar al carrito: " . $e->getMessage() . "</p>";
        }
    } else {
        $mensaje = "<p>Error: No has iniciado sesión.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Tienda de Belleza</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#4285f4">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
</head>
<body>
    <header>
        <div>
            <h1><a href="index.php">Tienda de Belleza</a></h1>
            <div class="header-controles">
                <a href="cerrar_sesion.php" class="boton-sesion">Cerrar Sesión</a>
                <a href="carrito.php" class="carrito-icon">
                    <img src="https://cdn-icons-png.flaticon.com/512/833/833314.png" alt="Carrito de compras">
                </a>
            </div>
        </div>
    </header>
    
    <main>
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-notificacion">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="contenedor-navegacion">
            <nav class="menu-navegacion">
                <a href="index.php" class="activo">Productos</a>
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                    <a href="agregar_producto.php">Agregar Producto</a>
                    <a href="confirmar_compra.php">Confirmar Compras</a>
                    <a href="ver_usuarios.php" class="btn-admin">Ver Usuarios</a>
                <?php endif; ?>
            </nav>
        </div>
        
        <h2>Productos Disponibles</h2>
        
        <div class="productos">
            <?php foreach ($productos as $producto): ?>
                <div class="producto">
                    <?php if (!empty($producto['imagen'])): ?>
                        <div class="producto-imagen">
                            <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        </div>
                    <?php else: ?>
                        <div class="producto-imagen producto-sin-imagen">
                            <div class="icono-producto"></div>
                        </div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
                    <p class="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                    <p class="precio">$<?php echo number_format($producto['precio'], 2); ?></p>
                    <form method="POST" action="">
                        <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                        <button type="submit" aria-label="Agregar <?php echo htmlspecialchars($producto['nombre']); ?> al carrito">
                            Agregar al Carrito
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <footer>
        <p>&copy; 2025 Tienda de Belleza. Todos los derechos reservados.</p>
    </footer>
    
    <script>
        // Esperar a que la página esté cargada
        document.addEventListener('DOMContentLoaded', function() {
            // Eliminar mensajes de notificación después de 5 segundos
            setTimeout(function() {
                const mensaje = document.querySelector('.mensaje-notificacion');
                if (mensaje) {
                    mensaje.style.opacity = '0';
                    setTimeout(function() {
                        mensaje.remove();
                    }, 500);
                }
            }, 5000);
            
            // Registrar service worker para PWA
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('service-worker.js')
                    .then(function() { console.log('Service Worker Registered'); });
            }
            
            // Manejar errores de carga de imágenes
            document.querySelectorAll('.producto-imagen img').forEach(function(img) {
                img.onerror = function() {
                    this.parentNode.classList.add('producto-sin-imagen');
                    this.parentNode.innerHTML = '<div class="icono-producto"></div>';
                };
            });
        });
    </script>
</body>
</html>