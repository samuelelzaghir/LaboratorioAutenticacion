<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Sanitizador.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
//use Sonata\GoogleAuthenticator\GoogleQrUrl;

class TwoFactorAuth
{
    private PDO $conexion;
    private GoogleAuthenticator $googleAuthenticator;
    private string $nombreApp = 'LaboratorioAutenticacion';

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
        $this->googleAuthenticator = new GoogleAuthenticator();
    }

    public function obtenerUsuarioPorId(int $idUsuario): ?array
    {
        $sql = "SELECT id_usuario, usuario, correo, secret_2fa 
                FROM usuarios 
                WHERE id_usuario = :id_usuario 
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':id_usuario' => $idUsuario
        ]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function generarORecuperarSecret(int $idUsuario): ?string
    {
        $usuario = $this->obtenerUsuarioPorId($idUsuario);

        if (!$usuario) {
            return null;
        }

        if (!empty($usuario['secret_2fa'])) {
            return $usuario['secret_2fa'];
        }

        $secret = $this->googleAuthenticator->generateSecret();
        $secret = Sanitizador::limpiarSecret2FA($secret);

        if ($secret === '') {
            return null;
        }

        $sql = "UPDATE usuarios 
                SET secret_2fa = :secret_2fa 
                WHERE id_usuario = :id_usuario";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':secret_2fa' => $secret,
            ':id_usuario' => $idUsuario
        ]);

        return $secret;
    }

    public function generarUrlQR(string $correo, string $secret): string
    {
        $label = rawurlencode($this->nombreApp . ':' . $correo);
        $issuer = rawurlencode($this->nombreApp);

        $otpauth = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}";

        return 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&margin=20&data=' . urlencode($otpauth);
    }
    
    public function verificarCodigo(string $secret, string $codigo): bool
    {
        $codigo = Sanitizador::limpiarCodigo2FA($codigo);

        if ($codigo === '') {
            return false;
        }

        return $this->googleAuthenticator->checkCode($secret, $codigo);
    }
}