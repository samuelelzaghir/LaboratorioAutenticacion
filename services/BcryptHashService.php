<?php

require_once __DIR__ . '/../interfaces/HashInterface.php';

class BcryptHashService implements HashInterface
{
    public function generarHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function verificarHash(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}