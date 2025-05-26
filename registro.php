<?php
// Archivo: registro.php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'usuario';

    // Validar datos
    if (!empty($nombre) && !empty($email) && !empty($password)) {
        if ($rol === 'admin') {
            // Verificar si ya hay 3 administradores
            $stmt = $pdo->query("SELECT COUNT(*) as total_admins FROM usuarios WHERE rol = 'admin'");
            $total_admins = $stmt->fetch()['total_admins'];

            if ($total_admins >= 3) {
                echo "<p>No se pueden crear más de 3 administradores.</p>";
                exit;
            }
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $email, $passwordHash, $rol]);
            echo "<p>Registro exitoso. Ahora puedes <a href='login.php'>iniciar sesión</a>.</p>";
        } catch (PDOException $e) {
            echo "<p>Error al registrar el usuario: " . $e->getMessage() . "</p>";
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
    <title>Registro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1><a href="index.php" style="text-decoration: none; color: inherit;">Registro de Usuario</a></h1>
    </header>
    <main>
        <form method="POST" action="" class="formulario">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="input-text" placeholder="Ingresa tu nombre" required>

            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" class="input-text" placeholder="Ingresa tu correo" required>

            <label for="password">Contraseña:</label>
            <div class="input-password">
                <input type="password" id="password" name="password" class="input-text" placeholder="Ingresa tu contraseña" required>
                <button type="button" class="ver-password" onclick="togglePassword('password')">👁️</button>
            </div>

            <label for="rol">Rol:</label>
            <select id="rol" name="rol" class="input-select">
                <option value="usuario">Usuario</option>
                <option value="admin">Administrador</option>
            </select>

            <button type="submit" class="btn-submit">Registrarse</button>
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