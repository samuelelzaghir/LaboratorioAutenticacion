<?php

class Conexion
{
    private string $host = 'localhost';
    private string $dbname = 'laboratorio_autenticacion';
    private string $usuario = 'auth_user';
    private string $password = 'AuthUser2026*';
    private string $charset = 'utf8mb4';

    public function conectar(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";

        try {
            $conexion = new PDO($dsn, $this->usuario, $this->password);

            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            return $conexion;
        } catch (PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error al conectar con la base de datos.");
        }
    }
}