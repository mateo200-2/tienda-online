<?php
session_start();
require 'conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Obtener datos del usuario a editar
if (!isset($_GET['id'])) {
    header('Location: ver_usuarios.php');
    exit();
}

$id = intval($_GET['id']);
$sql = "SELECT * FROM usuarios WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows !== 1) {
    header('Location: ver_usuarios.php');
    exit();
}
$usuario = $result->fetch_assoc();

// Procesar formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $rol = $conn->real_escape_string($_POST['rol']);
    $sql = "UPDATE usuarios SET nombre='$nombre', email='$email', rol='$rol' WHERE id=$id";
    if ($conn->query($sql)) {
        header('Location: ver_usuarios.php?msg=editado');
        exit();
    } else {
        $error = 'Error al actualizar el usuario.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container">
        <h2>Editar Usuario</h2>
        <?php if (isset($error)) echo '<p class="error">'.$error.'</p>'; ?>
        <form method="post">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
            <label>Rol</label>
            <select name="rol" required>
                <option value="usuario" <?php if($usuario['rol']==='usuario') echo 'selected'; ?>>Usuario</option>
                <option value="admin" <?php if($usuario['rol']==='admin') echo 'selected'; ?>>Administrador</option>
            </select>
            <button type="submit" class="btn">Guardar Cambios</button>
            <a href="ver_usuarios.php" class="btn-cancel">Cancelar</a>
        </form>
    </div>
</body>
</html>
