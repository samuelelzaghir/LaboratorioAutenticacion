<?php
session_start();

require_once __DIR__ . '/../classes/CSRFProtection.php';

$tokenCSRF = CSRFProtection::generarToken();

$errores = $_SESSION['errores_login'] ?? [];
$mensaje = $_SESSION['mensaje_login'] ?? '';

unset($_SESSION['errores_login'], $_SESSION['mensaje_login']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="../public/css/estilos.css">
</head>
<body>

    <main class="contenedor">
        <section class="tarjeta">
            <h1>Iniciar Sesión</h1>
            <p class="subtitulo">Ingresa tus credenciales para continuar con 2FA.</p>

            <?php if (!empty($errores)): ?>
                <div class="alerta error">
                    <?php foreach ($errores as $error): ?>
                        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($mensaje !== ''): ?>
                <div class="alerta exito">
                    <p><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="../controllers/procesar_login.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $tokenCSRF; ?>">

                <label for="usuario">Usuario o correo</label>
                <input type="text" id="usuario" name="usuario" required>

                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Continuar</button>
            </form>

            <p class="enlace">
                ¿No tienes cuenta? <a href="registro.php">Registrarse</a>
            </p>
        </section>
    </main>

</body>
</html>