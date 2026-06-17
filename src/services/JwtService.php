<?php
/**
 * DELICACY - JWT Service
 *
 * HS256 minimalista para tokens internos entre Admin Contratante, Editor Visual
 * e APIs do ecossistema de cardápio.
 */

class JwtService
{
    public static function issue(array $claims, $ttlSeconds = 3600)
    {
        $now = time();
        $payload = array_merge($claims, [
            'iat' => $now,
            'exp' => $now + (int)$ttlSeconds,
            'iss' => APP_NAME,
        ]);

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $segments = [
            self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];

        $segments[] = self::sign(implode('.', $segments));
        return implode('.', $segments);
    }

    public static function verify($token)
    {
        $parts = explode('.', (string)$token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        $expected = self::sign($header . '.' . $payload);

        if (!hash_equals($expected, $signature)) {
            return null;
        }

        $claims = json_decode(self::base64UrlDecode($payload), true);
        if (!is_array($claims) || (int)($claims['exp'] ?? 0) < time()) {
            return null;
        }

        return $claims;
    }

    private static function sign($data)
    {
        return self::base64UrlEncode(hash_hmac('sha256', $data, self::secret(), true));
    }

    private static function secret()
    {
        return getenv('JWT_SECRET') ?: hash('sha256', APP_ROOT . DB_NAME . APP_NAME);
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

