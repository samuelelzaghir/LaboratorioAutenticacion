<?php
session_start();

require_once __DIR__ . '/../classes/CSRFProtection.php';

$tokenCSRF = CSRFProtection::generarToken();

$errores = $_SESSION['errores_registro'] ?? [];
$exito = $_SESSION['exito_registro'] ?? '';

unset($_SESSION['errores_registro'], $_SESSION['exito_registro']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="../public/css/estilos.css">
</head>
<body>

    <main class="contenedor">
        <section class="tarjeta">
            <h1>Registro de Usuario</h1>
            <p class="subtitulo">Crea una cuenta para activar autenticación 2FA.</p>

            <?php if (!empty($errores)): ?>
                <div class="alerta error">
                    <?php foreach ($errores as $error): ?>
                        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($exito !== ''): ?>
                <div class="alerta exito">
                    <p><?php echo htmlspecialchars($exito, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="../controllers/procesar_registro.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $tokenCSRF; ?>">

                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" required>

                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" minlength="4" required>

                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" required>

                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" minlength="8" required>

                <label for="confirmar_password">Confirmar contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" minlength="8" required>

                <label for="sexo">Sexo</label>
                <select id="sexo" name="sexo" required>
                    <option value="">Seleccione una opción</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                    <option value="Otro">Otro</option>
                </select>

                <button type="submit">Registrarse</button>
            </form>

            <p class="enlace">
                ¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a>
            </p>
        </section>
    </main>

</body>
</html>