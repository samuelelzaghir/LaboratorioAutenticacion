<?php
session_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../classes/TwoFactorAuth.php';

if (empty($_SESSION['usuario_registrado_id']) || empty($_SESSION['fase_qr_pendiente'])) {
    header('Location: registro.php');
    exit;
}

$conexion = new Conexion();
$pdo = $conexion->conectar();

$twoFactor = new TwoFactorAuth($pdo);

$idUsuario = (int) $_SESSION['usuario_registrado_id'];
$usuario = $twoFactor->obtenerUsuarioPorId($idUsuario);

if (!$usuario) {
    session_destroy();
    header('Location: registro.php');
    exit;
}

$secret = $twoFactor->generarORecuperarSecret($idUsuario);
$qrUrl = $twoFactor->generarUrlQR($usuario['correo'], $secret);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activar 2FA</title>
    <link rel="stylesheet" href="../public/css/estilos.css">
</head>
<body>

    <main class="contenedor">
        <section class="tarjeta">
            <h1>Activar 2FA</h1>
            <p class="subtitulo">
                Escanea este código QR con Google Authenticator antes de iniciar sesión.
            </p>

            <div class="qr-contenedor">
                <img src="<?php echo htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Código QR 2FA">
            </div>

            <p class="subtitulo">
                Usuario: <?php echo htmlspecialchars($usuario['usuario'], ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <p class="subtitulo">
                Correo: <?php echo htmlspecialchars($usuario['correo'], ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <a class="boton-enlace" href="login.php">Ir al login</a>
        </section>
    </main>

</body>
</html>