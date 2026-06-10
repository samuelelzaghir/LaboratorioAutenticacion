<?php
session_start();

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../classes/CSRFProtection.php';
require_once __DIR__ . '/../classes/RegistroUsuario.php';
require_once __DIR__ . '/../services/BcryptHashService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/registro.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';

if (!CSRFProtection::verificarToken($token)) {
    $_SESSION['errores_registro'] = ['Token CSRF inválido. Intente nuevamente.'];
    header('Location: ../views/registro.php');
    exit;
}

$conexion = new Conexion();
$pdo = $conexion->conectar();

$hashService = new BcryptHashService();
$registroUsuario = new RegistroUsuario($pdo, $hashService);

$resultado = $registroUsuario->registrar($_POST);

if (!$resultado['exito']) {
    $_SESSION['errores_registro'] = $resultado['errores'];
    header('Location: ../views/registro.php');
    exit;
}

$_SESSION['exito_registro'] = $resultado['mensaje'];
$_SESSION['usuario_registrado_id'] = $resultado['id_usuario'];

header('Location: ../views/registro.php');
exit;