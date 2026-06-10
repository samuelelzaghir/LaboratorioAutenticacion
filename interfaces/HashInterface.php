<?php

interface HashInterface
{
    public function generarHash(string $password): string;

    public function verificarHash(string $password, string $hash): bool;
}