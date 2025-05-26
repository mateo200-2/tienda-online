<?php
session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: index.php"); // Redirigir al inicio si no es administrador
    exit;
}

include 'conexion.php';

try {
    // Obtener todos los usuarios registrados
    $stmt = $pdo->query("SELECT id, nombre, email, rol FROM usuarios");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error al obtener los usuarios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Usuarios</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos específicos para el panel de administración */
        .admin-panel {
            background-color: var(--color-fondo);
            border-radius: var(--radio-borde);
            box-shadow: var(--sombra-suave);
            padding: 30px;
            margin-bottom: 60px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--color-borde);
        }

        .admin-titulo {
            font-size: 1.5rem;
            color: var(--color-texto);
            margin: 0;
        }

        .volver-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background-color: var(--color-secundario);
            color: var(--color-primario);
            border-radius: var(--radio-borde);
            font-weight: 500;
            transition: var(--transicion);
        }

        .volver-btn:hover {
            background-color: var(--color-primario);
            color: white;
            transform: translateY(-2px);
        }

        .volver-btn::before {
            content: "←";
            margin-right: 8px;
        }

        .tabla-usuarios {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--color-fondo);
            border-radius: var(--radio-borde);
            overflow: hidden;
        }

        .tabla-usuarios thead {
            background-color: var(--color-secundario);
        }

        .tabla-usuarios th {
            text-align: left;
            padding: 15px;
            font-weight: 600;
            color: var(--color-texto);
            border-bottom: 2px solid var(--color-primario);
        }

        .tabla-usuarios td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--color-borde);
        }

        .tabla-usuarios tr:last-child td {
            border-bottom: none;
        }

        .tabla-usuarios tr:hover {
            background-color: var(--color-fondo-alt);
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
        }

        .badge-admin {
            background-color: var(--color-primario);
            color: white;
        }

        .badge-cliente {
            background-color: #e0f2fe;
            color: #0284c7;
        }

        .acciones {
            display: flex;
            gap: 10px;
        }

        .btn-accion {
            padding: 5px 10px;
            border-radius: var(--radio-borde);
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transicion);
            border: none;
        }

        .btn-editar {
            background-color: #f0f9ff;
            color: #0284c7;
        }

        .btn-editar:hover {
            background-color: #0284c7;
            color: white;
        }

        .btn-eliminar {
            background-color: #fef2f2;
            color: #ef4444;
        }

        .btn-eliminar:hover {
            background-color: #ef4444;
            color: white;
        }

        .panel-estadisticas {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .tarjeta-estadistica {
            background-color: var(--color-fondo);
            border-radius: var(--radio-borde);
            padding: 20px;
            box-shadow: var(--sombra-suave);
            text-align: center;
            transition: var(--transicion);
        }

        .tarjeta-estadistica:hover {
            box-shadow: var(--sombra-media);
            transform: translateY(-3px);
        }

        .tarjeta-estadistica h3 {
            font-size: 0.9rem;
            color: var(--color-texto-claro);
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .tarjeta-estadistica p {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-primario);
            margin: 0;
        }

        .btn-nuevo {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--color-primario);
            color: white;
            border-radius: var(--radio-borde);
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            transition: var(--transicion);
        }

        .btn-nuevo:hover {
            background-color: var(--color-acento);
            transform: translateY(-2px);
        }

        /* Responsive para la tabla */
        @media (max-width: 768px) {
            .tabla-usuarios {
                display: block;
                overflow-x: auto;
            }

            .panel-estadisticas {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .panel-estadisticas {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header>
        <div>
            <h1><a href="index.php">Tienda de Belleza</a></h1>
            <div class="header-controles">
                <a href="panel_admin.php" class="boton-sesion">Panel Admin</a>
                <a href="index.php" class="boton-sesion">Tienda</a>
            </div>
        </div>
    </header>

    <main>
        <div class="admin-panel">
            <div class="admin-header">
                <h2 class="admin-titulo">Panel de Administración</h2>
                <a href="panel_admin.php" class="volver-btn">Volver al panel</a>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="panel-estadisticas">
                <div class="tarjeta-estadistica">
                    <h3>Total Usuarios</h3>
                    <p><?php echo count($usuarios); ?></p>
                </div>
                <div class="tarjeta-estadistica">
                    <h3>Administradores</h3>
                    <p><?php 
                        $admins = array_filter($usuarios, function($u) {
                            return $u['rol'] === 'admin';
                        });
                        echo count($admins);
                    ?></p>
                </div>
                <div class="tarjeta-estadistica">
                    <h3>Clientes</h3>
                    <p><?php 
                        $clientes = array_filter($usuarios, function($u) {
                            return $u['rol'] === 'cliente';
                        });
                        echo count($clientes);
                    ?></p>
                </div>
            </div>

            <a href="nuevo_usuario.php" class="btn-nuevo">+ Añadir Usuario</a>

            <table class="tabla-usuarios">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                            <td>
                                <?php if ($usuario['rol'] === 'admin'): ?>
                                    <span class="badge badge-admin">Admin</span>
                                <?php else: ?>
                                    <span class="badge badge-cliente">Cliente</span>
                                <?php endif; ?>
                            </td>
                            <td class="acciones">
                                <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn-accion btn-editar">Editar</a>
                                <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                    <a href="eliminar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn-accion btn-eliminar" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Tienda de Belleza. Todos los derechos reservados.</p>
    </footer>
</body>
</html>