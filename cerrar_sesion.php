<?php
// Archivo: cerrar_sesion.php
session_start();

// Destruir todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

echo "<p>Sesión cerrada exitosamente.</p>";

// Redirigir al inicio
header("Location: login.php");
exit;
?>