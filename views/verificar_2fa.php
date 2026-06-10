<?php
session_start();

require_once __DIR__ . '/../classes/CSRFProtection.php';

if (empty($_SESSION['usuario_2fa_id']) || empty($_SESSION['fase_credenciales_correctas'])) {
    header('Location: login.php');
    exit;
}

$tokenCSRF = CSRFProtection::generarToken();

$errores = $_SESSION['errores_2fa'] ?? [];
unset($_SESSION['errores_2fa']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación 2FA</title>
    <link rel="stylesheet" href="../public/css/estilos.css">
</head>
<body>

    <main class="contenedor">
        <section class="tarjeta">
            <h1>Verificación 2FA</h1>
            <p class="subtitulo">
                Ingresa el código temporal generado por Google Authenticator.
            </p>

            <?php if (!empty($errores)): ?>
                <div class="alerta error">
                    <?php foreach ($errores as $error): ?>
                        <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="../controllers/procesar_2fa.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?php echo $tokenCSRF; ?>">

                <label for="codigo_2fa">Código 2FA</label>
                <input 
                    type="text" 
                    id="codigo_2fa" 
                    name="codigo_2fa" 
                    maxlength="6" 
                    pattern="[0-9]{6}" 
                    required
                >

                <button type="submit">Verificar código</button>
            </form>
        </section>
    </main>

</body>
</html>