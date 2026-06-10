<?php

class Sanitizador
{
    public static function limpiarTexto(string $valor): string
    {
        $valor = trim($valor);
        $valor = strip_tags($valor);
        $valor = preg_replace('/\s+/', ' ', $valor);

        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }

    public static function limpiarUsuario(string $valor): string
    {
        $valor = trim($valor);
        $valor = strip_tags($valor);

        return preg_replace('/[^a-zA-Z0-9_]/', '', $valor);
    }

    public static function limpiarCorreo(string $correo): string
    {
        $correo = trim($correo);
        $correo = filter_var($correo, FILTER_SANITIZE_EMAIL);

        return strtolower($correo);
    }

    public static function validarCorreo(string $correo): bool
    {
        return filter_var($correo, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function limpiarSexo(string $sexo): string
    {
        $sexo = trim($sexo);

        $opcionesPermitidas = ['M', 'F', 'Otro'];

        if (!in_array($sexo, $opcionesPermitidas, true)) {
            return '';
        }

        return $sexo;
    }

    public static function limpiarCodigo2FA(string $codigo): string
    {
        return preg_replace('/[^0-9]/', '', trim($codigo));
    }

    public static function limpiarSecret2FA(string $secret): string
    {
        $secret = trim($secret);

        if (!preg_match('/^[A-Z2-7=]+$/', $secret)) {
            return '';
        }

        return $secret;
    }
}