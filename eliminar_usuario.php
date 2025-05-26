<?php
session_start();
require 'conexion.php';

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Verificar que se recibe el id del usuario a eliminar
if (!isset($_GET['id'])) {
    header('Location: ver_usuarios.php');
    exit();
}

$id = intval($_GET['id']);
// No permitir que un admin se elimine a sí mismo
if ($id == $_SESSION['usuario_id']) {
    header('Location: ver_usuarios.php?error=no_self_delete');
    exit();
}

$sql = "DELETE FROM usuarios WHERE id = $id";
if ($conn->query($sql)) {
    header('Location: ver_usuarios.php?msg=eliminado');
    exit();
} else {
    header('Location: ver_usuarios.php?error=delete_failed');
    exit();
}
?>
