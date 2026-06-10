<?php

require_once __DIR__ . '/Sanitizador.php';
require_once __DIR__ . '/../services/BcryptHashService.php';

class RegistroUsuario
{
    private PDO $conexion;
    private HashInterface $hashService;

    public function __construct(PDO $conexion, HashInterface $hashService)
    {
        $this->conexion = $conexion;
        $this->hashService = $hashService;
    }

    public function registrar(array $datos): array
    {
        $nombre = Sanitizador::limpiarTexto($datos['nombre'] ?? '');
        $apellido = Sanitizador::limpiarTexto($datos['apellido'] ?? '');
        $usuario = Sanitizador::limpiarUsuario($datos['usuario'] ?? '');
        $correo = Sanitizador::limpiarCorreo($datos['correo'] ?? '');
        $password = trim($datos['password'] ?? '');
        $confirmarPassword = trim($datos['confirmar_password'] ?? '');
        $sexo = Sanitizador::limpiarSexo($datos['sexo'] ?? '');

        $errores = $this->validarDatos(
            $nombre,
            $apellido,
            $usuario,
            $correo,
            $password,
            $confirmarPassword,
            $sexo
        );

        if (!empty($errores)) {
            return [
                'exito' => false,
                'errores' => $errores
            ];
        }

        if ($this->existeUsuarioOCorreo($usuario, $correo)) {
            return [
                'exito' => false,
                'errores' => ['El usuario o correo ya está registrado.']
            ];
        }

        $hashPassword = $this->hashService->generarHash($password);

        $sql = "INSERT INTO usuarios 
                (nombre, apellido, usuario, correo, hash_magic, sexo) 
                VALUES 
                (:nombre, :apellido, :usuario, :correo, :hash_magic, :sexo)";

        $stmt = $this->conexion->prepare($sql);

        $resultado = $stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':usuario' => $usuario,
            ':correo' => $correo,
            ':hash_magic' => $hashPassword,
            ':sexo' => $sexo
        ]);

        if (!$resultado) {
            return [
                'exito' => false,
                'errores' => ['No se pudo registrar el usuario.']
            ];
        }

        return [
            'exito' => true,
            'mensaje' => 'Usuario registrado correctamente.',
            'id_usuario' => $this->conexion->lastInsertId()
        ];
    }

    private function validarDatos(
        string $nombre,
        string $apellido,
        string $usuario,
        string $correo,
        string $password,
        string $confirmarPassword,
        string $sexo
    ): array {
        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }

        if ($apellido === '') {
            $errores[] = 'El apellido es obligatorio.';
        }

        if ($usuario === '') {
            $errores[] = 'El usuario es obligatorio.';
        }

        if (strlen($usuario) < 4) {
            $errores[] = 'El usuario debe tener al menos 4 caracteres.';
        }

        if ($correo === '') {
            $errores[] = 'El correo es obligatorio.';
        }

        if (!Sanitizador::validarCorreo($correo)) {
            $errores[] = 'El correo no tiene un formato válido.';
        }

        if ($password === '') {
            $errores[] = 'La contraseña es obligatoria.';
        }

        if (strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener mínimo 8 caracteres.';
        }

        if ($password !== $confirmarPassword) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if ($sexo === '') {
            $errores[] = 'Debe seleccionar un sexo válido.';
        }

        return $errores;
    }

    private function existeUsuarioOCorreo(string $usuario, string $correo): bool
    {
        $sql = "SELECT id_usuario 
                FROM usuarios 
                WHERE usuario = :usuario OR correo = :correo 
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuario,
            ':correo' => $correo
        ]);

        return $stmt->fetch() !== false;
    }
}