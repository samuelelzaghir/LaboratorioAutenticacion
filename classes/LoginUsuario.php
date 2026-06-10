<?php

require_once __DIR__ . '/Sanitizador.php';

class LoginUsuario
{
    private PDO $conexion;
    private HashInterface $hashService;

    public function __construct(PDO $conexion, HashInterface $hashService)
    {
        $this->conexion = $conexion;
        $this->hashService = $hashService;
    }

    public function autenticar(string $usuarioOCorreo, string $password): array
    {
        $usuarioOCorreo = Sanitizador::limpiarCorreo($usuarioOCorreo);

        if ($usuarioOCorreo === '' || $password === '') {
            $this->registrarIntento($usuarioOCorreo, 'fallido');

            return [
                'exito' => false,
                'mensaje' => 'Debe ingresar usuario/correo y contraseña.'
            ];
        }

        $usuario = $this->buscarUsuario($usuarioOCorreo);

        if (!$usuario) {
            $this->registrarIntento($usuarioOCorreo, 'fallido');

            return [
                'exito' => false,
                'mensaje' => 'Usuario o contraseña incorrectos.'
            ];
        }

        if (!$this->hashService->verificarHash($password, $usuario['hash_magic'])) {
            $this->registrarIntento($usuarioOCorreo, 'fallido');

            return [
                'exito' => false,
                'mensaje' => 'Usuario o contraseña incorrectos.'
            ];
        }

        $this->registrarIntento($usuario['usuario'], 'pendiente_2fa');

        return [
            'exito' => true,
            'usuario' => $usuario
        ];
    }

    private function buscarUsuario(string $usuarioOCorreo): ?array
    {
        $sql = "SELECT id_usuario, nombre, apellido, usuario, correo, hash_magic, secret_2fa
                FROM usuarios
                WHERE usuario = :usuario OR correo = :correo
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuarioOCorreo,
            ':correo' => $usuarioOCorreo
        ]);

        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function registrarIntento(string $usuario, string $estado): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';

        $sql = "INSERT INTO intentos_login (usuario, estado, ip)
                VALUES (:usuario, :estado, :ip)";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuario,
            ':estado' => $estado,
            ':ip' => $ip
        ]);
    }
}