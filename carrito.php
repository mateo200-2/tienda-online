<?php
session_start();
include 'conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

// Procesar actualización de cantidad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        $producto_id = $_POST['producto_id'];
        
        // Actualizar cantidad
        if ($_POST['accion'] === 'aumentar') {
            $stmt = $pdo->prepare("UPDATE carrito SET cantidad = cantidad + 1 WHERE usuario_id = ? AND producto_id = ?");
            $stmt->execute([$usuario_id, $producto_id]);
        } else if ($_POST['accion'] === 'disminuir') {
            $stmt = $pdo->prepare("UPDATE carrito SET cantidad = cantidad - 1 WHERE usuario_id = ? AND producto_id = ? AND cantidad > 1");
            $stmt->execute([$usuario_id, $producto_id]);
        } else if ($_POST['accion'] === 'eliminar') {
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?");
            $stmt->execute([$usuario_id, $producto_id]);
            $mensaje = "<p>Producto eliminado del carrito.</p>";
        }
        
        // Redirigir para evitar reenvío de formulario
        header('Location: carrito.php');
        exit;
    } else if (isset($_POST['finalizar_compra'])) {
        // Verificar si hay productos en el carrito
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM carrito WHERE usuario_id = ?");
        $stmt->execute([$usuario_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            try {
                // Iniciar transacción
                $pdo->beginTransaction();
                
                // Crear pedido
                $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, fecha, estado) VALUES (?, NOW(), 'pendiente')");
                $stmt->execute([$usuario_id]);
                $pedido_id = $pdo->lastInsertId();
                
                // Obtener productos del carrito
                $stmt = $pdo->prepare("
                    SELECT c.producto_id, c.cantidad, p.precio 
                    FROM carrito c 
                    JOIN productos p ON c.producto_id = p.id 
                    WHERE c.usuario_id = ?
                ");
                $stmt->execute([$usuario_id]);
                $items = $stmt->fetchAll();
                
                // Agregar items al pedido
                $stmt_detalle = $pdo->prepare("INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
                
                foreach ($items as $item) {
                    $stmt_detalle->execute([
                        $pedido_id, 
                        $item['producto_id'], 
                        $item['cantidad'], 
                        $item['precio']
                    ]);
                }
                
                // Vaciar carrito
                $stmt = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
                $stmt->execute([$usuario_id]);
                
                // Confirmar transacción
                $pdo->commit();
                
                $mensaje = "<p>¡Compra realizada con éxito! Tu número de pedido es: #" . $pedido_id . "</p>";
            } catch (PDOException $e) {
                // Revertir transacción en caso de error
                $pdo->rollBack();
                $mensaje = "<p>Error al procesar la compra: " . $e->getMessage() . "</p>";
            }
        } else {
            $mensaje = "<p>Tu carrito está vacío.</p>";
        }
    }
}

// Obtener productos en el carrito
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.nombre, p.precio, p.imagen
        FROM carrito c
        JOIN productos p ON c.producto_id = p.id
        WHERE c.usuario_id = ?
    ");
    $stmt->execute([$usuario_id]);
    $productos_carrito = $stmt->fetchAll();
    
    // Calcular totales
    $total = 0;
    foreach ($productos_carrito as $producto) {
        $total += $producto['precio'] * $producto['cantidad'];
    }
} catch (PDOException $e) {
    die("Error al obtener el carrito: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Carrito de Compras - Tienda de Belleza</title>
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
                <a href="carrito.php" class="carrito-icon" data-items="<?php echo count($productos_carrito); ?>">
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
                <a href="index.php">Productos</a>
                <a href="carrito.php" class="activo">Carrito</a>
                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                    <a href="agregar_producto.php">Agregar Producto</a>
                    <a href="confirmar_compra.php">Confirmar Compras</a>
                <?php endif; ?>
            </nav>
        </div>
        
        <h2>Tu Carrito de compras</h2>
        
        <div class="carrito-contenedor">
            <?php if (empty($productos_carrito)): ?>
                <div class="carrito-vacio">
                    <img src="https://cdn-icons-png.flaticon.com/512/2038/2038854.png" alt="Carrito vacío">
                    <p>Tu carrito está vacío</p>
                    <a href="index.php">Ir a la tienda</a>
                </div>
            <?php else: ?>
                <div class="carrito-items">
                    <?php foreach ($productos_carrito as $producto): ?>
                        <div class="carrito-item">
                            <div class="carrito-item-imagen">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                                <?php else: ?>
                                    <div class="icono-producto"></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="carrito-item-detalles">
                                <h4><?php echo htmlspecialchars($producto['nombre']); ?></h4>
                                <p class="precio">$<?php echo number_format($producto['precio'], 2); ?></p>
                            </div>
                            
                            <div class="carrito-item-cantidad">
                                <form method="POST" action="" class="cantidad-form">
                                    <input type="hidden" name="producto_id" value="<?php echo $producto['producto_id']; ?>">
                                    <input type="hidden" name="accion" value="disminuir">
                                    <button type="submit" aria-label="Disminuir cantidad">-</button>
                                </form>
                                
                                <span><?php echo $producto['cantidad']; ?></span>
                                
                                <form method="POST" action="" class="cantidad-form">
                                    <input type="hidden" name="producto_id" value="<?php echo $producto['producto_id']; ?>">
                                    <input type="hidden" name="accion" value="aumentar">
                                    <button type="submit" aria-label="Aumentar cantidad">+</button>
                                </form>
                            </div>
                            
                            <form method="POST" action="" class="eliminar-form">
                                <input type="hidden" name="producto_id" value="<?php echo $producto['producto_id']; ?>">
                                <input type="hidden" name="accion" value="eliminar">
                                <button type="submit" class="carrito-item-eliminar" aria-label="Eliminar del carrito">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="carrito-resumen">
                    <h3>Resumen del pedido</h3>
                    <div class="carrito-total">
                        <span>Total:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="finalizar_compra" value="1">
                        <button type="submit" class="boton-finalizar">Finalizar Compra</button>
                    </form>
                    
                    <a href="index.php" class="boton-seguir-comprando">Seguir comprando</a>
                </div>
            <?php endif; ?>
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
        });
    </script>
</body>
</html>