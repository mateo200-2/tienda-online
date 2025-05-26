<?php
// Archivo: confirmar_compra.php
include 'conexion.php';
session_start();

// Obtener los productos del carrito
try {
    $stmt = $pdo->query("SELECT c.id, p.nombre, p.precio, c.cantidad FROM carrito c JOIN productos p ON c.producto_id = p.id");
    $carrito = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener el carrito: " . $e->getMessage());
}

// Procesar la compra
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'] ?? 1; // Usuario por defecto si no hay sesión
    $total = array_reduce($carrito, function ($sum, $item) {
        return $sum + ($item['precio'] * $item['cantidad']);
    }, 0);

    try {
        // Insertar el pedido
        $stmt = $pdo->prepare("INSERT INTO pedidos (usuario_id, fecha, total) VALUES (?, NOW(), ?)");
        $stmt->execute([$usuario_id, $total]);
        $pedido_id = $pdo->lastInsertId();

        // Insertar los detalles del pedido
        foreach ($carrito as $item) {
            $stmt = $pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
            $stmt->execute([$pedido_id, $item['id'], $item['cantidad'], $item['precio']]);
        }

        // Vaciar el carrito
        $pdo->query("DELETE FROM carrito");

        echo "<p>Compra realizada con éxito. Gracias por tu compra.</p>";
    } catch (PDOException $e) {
        echo "<p>Error al procesar la compra: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Compra</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php" style="text-decoration: none; color: inherit;">Confirmar Compra</a></h1>
    </header>
    <main>
        <h2>Resumen de Compra</h2>
        <div class="carrito">
            <?php if (empty($carrito)): ?>
                <p>No hay productos en el carrito.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carrito as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                <td>$<?php echo number_format($item['precio'], 2); ?></td>
                                <td><?php echo $item['cantidad']; ?></td>
                                <td>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <form method="POST" action="">
                    <button type="submit">Confirmar Compra</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>