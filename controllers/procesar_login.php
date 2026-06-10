<?php
session_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../classes/CSRFProtection.php';
require_once __DIR__ . '/../interfaces/HashInterface.php';
require_once __DIR__ . '/../services/BcryptHashService.php';
require_once __DIR__ . '/../classes/LoginUsuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';

if (!CSRFProtection::verificarToken($token)) {
    $_SESSION['errores_login'] = ['Token CSRF inválido. Intente nuevamente.'];
    header('Location: ../views/login.php');
    exit;
}

$usuarioOCorreo = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

$conexion = new Conexion();
$pdo = $conexion->conectar();

$hashService = new BcryptHashService();
$loginUsuario = new LoginUsuario($pdo, $hashService);

$resultado = $loginUsuario->autenticar($usuarioOCorreo, $password);

if (!$resultado['exito']) {
    $_SESSION['errores_login'] = [$resultado['mensaje']];
    header('Location: ../views/login.php');
    exit;
}

$usuario = $resultado['usuario'];

if (empty($usuario['secret_2fa'])) {
    $_SESSION['errores_login'] = ['El usuario no tiene 2FA activado.'];
    header('Location: ../views/login.php');
    exit;
}

$_SESSION['usuario_2fa_id'] = $usuario['id_usuario'];
$_SESSION['usuario_2fa_nombre'] = $usuario['usuario'];
$_SESSION['fase_credenciales_correctas'] = true;

header('Location: ../views/verificar_2fa.php');
exit;