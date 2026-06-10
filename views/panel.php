<?php
session_start();

if (empty($_SESSION['usuario_autenticado_id']) || empty($_SESSION['acceso_confirmado_2fa'])) {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario_autenticado_nombre'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Protegido</title>
    <link rel="stylesheet" href="../public/css/estilos.css">
</head>
<body>

    <main class="contenedor">
        <section class="tarjeta">
            <h1>Acceso concedido</h1>
            <p class="subtitulo">
                Bienvenido, <?php echo htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8'); ?>.
            </p>

            <p class="subtitulo">
                Has iniciado sesión correctamente con contraseña y autenticación de dos factores.
            </p>

            <a class="boton-enlace" href="../controllers/logout.php">Cerrar sesión</a>
        </section>
    </main>

</body>
</html>