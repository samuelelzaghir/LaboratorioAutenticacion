<?php
session_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../classes/CSRFProtection.php';
require_once __DIR__ . '/../classes/TwoFactorAuth.php';
require_once __DIR__ . '/../classes/LoginUsuario.php';
require_once __DIR__ . '/../interfaces/HashInterface.php';
require_once __DIR__ . '/../services/BcryptHashService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/verificar_2fa.php');
    exit;
}

if (empty($_SESSION['usuario_2fa_id']) || empty($_SESSION['fase_credenciales_correctas'])) {
    header('Location: ../views/login.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';

if (!CSRFProtection::verificarToken($token)) {
    $_SESSION['errores_2fa'] = ['Token CSRF inválido. Intente nuevamente.'];
    header('Location: ../views/verificar_2fa.php');
    exit;
}

$codigo = $_POST['codigo_2fa'] ?? '';

$conexion = new Conexion();
$pdo = $conexion->conectar();

$twoFactor = new TwoFactorAuth($pdo);
$hashService = new BcryptHashService();
$loginUsuario = new LoginUsuario($pdo, $hashService);

$idUsuario = (int) $_SESSION['usuario_2fa_id'];
$usuario = $twoFactor->obtenerUsuarioPorId($idUsuario);

if (!$usuario || empty($usuario['secret_2fa'])) {
    $_SESSION['errores_2fa'] = ['No se pudo validar el 2FA del usuario.'];
    header('Location: ../views/verificar_2fa.php');
    exit;
}

if (!$twoFactor->verificarCodigo($usuario['secret_2fa'], $codigo)) {
    $loginUsuario->registrarIntento($usuario['usuario'], 'fallido_2fa');

    $_SESSION['errores_2fa'] = ['Código 2FA incorrecto.'];
    header('Location: ../views/verificar_2fa.php');
    exit;
}

$loginUsuario->registrarIntento($usuario['usuario'], 'exitoso');

$_SESSION['usuario_autenticado_id'] = $usuario['id_usuario'];
$_SESSION['usuario_autenticado_nombre'] = $usuario['usuario'];
$_SESSION['acceso_confirmado_2fa'] = true;

unset($_SESSION['usuario_2fa_id'], $_SESSION['usuario_2fa_nombre'], $_SESSION['fase_credenciales_correctas']);

header('Location: ../views/panel.php');
exit;