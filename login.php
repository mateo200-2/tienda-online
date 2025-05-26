<?php
// Archivo: login.php
include 'conexion.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario['password'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol']; // Guardar el rol en la sesión
                header("Location: index.php"); // Redirigir al index
                exit;
            } else {
                echo "<p>Correo o contraseña incorrectos.</p>";
            }
        } catch (PDOException $e) {
            echo "<p>Error al iniciar sesión: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>Por favor, completa todos los campos.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php" style="text-decoration: none; color: inherit;">Iniciar Sesión</a></h1>
    </header>
    <main>
        <form method="POST" action="" class="formulario">
            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" class="input-text" placeholder="Ingresa tu correo" required>

            <label for="password">Contraseña:</label>
            <div class="input-password">
                <input type="password" id="password" name="password" class="input-text" placeholder="Ingresa tu contraseña" required>
                <button type="button" class="ver-password" onclick="togglePassword('password')">👁️</button>
            </div>

            <button type="submit" class="btn-submit">Iniciar Sesión</button>
        </form>

        <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }
        </script>
    </main>
</body>
</html>