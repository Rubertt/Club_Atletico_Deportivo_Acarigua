<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Implementación mínima de JWT con HS256 (sin dependencias externas).
 */
final class JWT
{
    public static function encode(array $payload, ?string $secret = null): string
    {
        $secret ??= (string) config('auth.jwt.secret');

        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $segments = [
            self::base64UrlEncode((string) json_encode($header)),
            self::base64UrlEncode((string) json_encode($payload)),
        ];
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, $secret, true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    public static function decode(string $jwt, ?string $secret = null): array
    {
        $secret ??= (string) config('auth.jwt.secret');

        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new RuntimeException('Formato de token inválido.');
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        $header = json_decode((string) self::base64UrlDecode($headerB64), true);
        if (!is_array($header) || ($header['alg'] ?? null) !== 'HS256') {
            throw new RuntimeException('Algoritmo de token no soportado.');
        }

        $expected = hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true);
        $actual   = (string) self::base64UrlDecode($signatureB64);
        if (!hash_equals($expected, $actual)) {
            throw new RuntimeException('Firma de token inválida.');
        }

        $payload = json_decode((string) self::base64UrlDecode($payloadB64), true);
        if (!is_array($payload)) {
            throw new RuntimeException('Payload de token inválido.');
        }

        $now = time();
        if (isset($payload['exp']) && $now >= (int) $payload['exp']) {
            throw new RuntimeException('Token expirado.');
        }
        if (isset($payload['nbf']) && $now < (int) $payload['nbf']) {
            throw new RuntimeException('Token aún no es válido.');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string|false
    {
        $pad = 4 - (strlen($data) % 4);
        if ($pad < 4) {
            $data .= str_repeat('=', $pad);
        }
        return base64_decode(strtr($data, '-_', '+/'), true);
    }
}
