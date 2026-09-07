<?php

namespace App\Support;

/**
 * Verifieert het gedeelde wuppo.dev SSO-token. Zelfde formaat als
 * hello.wuppo.dev/src/Jwt.php en match. Geen externe dependency.
 *
 * Twee algoritmes:
 *  - HS256: legacy, gedeeld SSO_SECRET.
 *  - RS256: hello tekent met de privésleutel; wij verifiëren met de publieke
 *    sleutel uit hello's /.well-known/jwks.json (gecachet op schijf).
 *
 * verify() kiest het pad strikt op de header-`alg` → geen HS/RS-confusion.
 */
class WuppoSso
{
    /**
     * @return array<string,mixed>|null null bij ongeldig/verlopen token
     */
    public static function verify(string $jwt, ?string $secret, ?string $jwksUrl = null): ?array
    {
        if ($jwt === '') {
            return null;
        }
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return null;
        }
        [$h64, $p64, $s64] = $parts;

        $header = json_decode(self::b64d($h64), true);
        if (! is_array($header)) {
            return null;
        }
        $alg     = (string) ($header['alg'] ?? '');
        $signing = "{$h64}.{$p64}";

        if ($alg === 'HS256') {
            if ($secret === null || $secret === '') {
                return null;
            }
            $expected = self::b64(hash_hmac('sha256', $signing, $secret, true));
            if (! hash_equals($expected, $s64)) {
                return null;
            }
        } elseif ($alg === 'RS256') {
            if ($jwksUrl === null || $jwksUrl === '') {
                return null;
            }
            $pem = self::publicKeyPem($jwksUrl, (string) ($header['kid'] ?? ''));
            if ($pem === null) {
                return null;
            }
            $key = openssl_pkey_get_public($pem);
            if ($key === false
                || openssl_verify($signing, self::b64d($s64), $key, OPENSSL_ALGO_SHA256) !== 1) {
                return null;
            }
        } else {
            return null; // onbekend of 'none' → weiger
        }

        $claims = json_decode(self::b64d($p64), true);
        if (! is_array($claims)) {
            return null;
        }
        $now = time();
        if (isset($claims['exp']) && $now >= (int) $claims['exp']) {
            return null;
        }
        if (isset($claims['nbf']) && $now < (int) $claims['nbf']) {
            return null;
        }

        return $claims;
    }

    // ---- JWKS ----

    /** Zoek de JWK met dit kid en bouw er een PEM-publieke-sleutel van. */
    private static function publicKeyPem(string $jwksUrl, string $kid): ?string
    {
        $jwk = self::findKey(self::jwks($jwksUrl, false), $kid);
        if ($jwk === null) {
            // kid onbekend → één keer vers ophalen (sleutelrotatie).
            $jwk = self::findKey(self::jwks($jwksUrl, true), $kid);
        }
        if ($jwk === null || ! isset($jwk['n'], $jwk['e'])) {
            return null;
        }
        return self::jwkToPem((string) $jwk['n'], (string) $jwk['e']);
    }

    /** @param array<int,array<string,mixed>> $keys */
    private static function findKey(array $keys, string $kid): ?array
    {
        foreach ($keys as $k) {
            if (($k['kty'] ?? '') !== 'RSA') {
                continue;
            }
            if ($kid === '' || ($k['kid'] ?? '') === $kid) {
                return $k;
            }
        }
        return null;
    }

    /**
     * Haal de JWK-set op, met een simpele on-disk cache (1u TTL).
     * @return array<int,array<string,mixed>>
     */
    private static function jwks(string $url, bool $forceRefresh): array
    {
        $dir = sys_get_temp_dir();
        if (function_exists('storage_path')) {
            try {
                $sp = storage_path('framework/cache');
                if (is_string($sp) && $sp !== '' && is_dir($sp)) {
                    $dir = $sp;
                }
            } catch (\Throwable $e) {
                // val terug op sys_get_temp_dir()
            }
        }
        $file = $dir.'/wuppo-jwks.json';
        $ttl  = 3600;

        if (! $forceRefresh && is_file($file) && (time() - filemtime($file)) < $ttl) {
            $cached = json_decode((string) @file_get_contents($file), true);
            if (is_array($cached['keys'] ?? null)) {
                return $cached['keys'];
            }
        }

        $ctx = stream_context_create(['http' => ['timeout' => 3], 'https' => ['timeout' => 3]]);
        $raw = @file_get_contents($url, false, $ctx);
        $data = is_string($raw) ? json_decode($raw, true) : null;
        if (is_array($data['keys'] ?? null)) {
            @file_put_contents($file, $raw, LOCK_EX);
            return $data['keys'];
        }

        // Fetch faalde → val terug op een (mogelijk verlopen) cache.
        if (is_file($file)) {
            $cached = json_decode((string) @file_get_contents($file), true);
            if (is_array($cached['keys'] ?? null)) {
                return $cached['keys'];
            }
        }
        return [];
    }

    /** Bouw een SPKI-PEM uit de base64url RSA-modulus (n) en exponent (e). */
    private static function jwkToPem(string $n64, string $e64): ?string
    {
        $n = self::b64d($n64);
        $e = self::b64d($e64);
        if ($n === '' || $e === '') {
            return null;
        }
        $rsaPublicKey = self::derSeq(self::derInt($n).self::derInt($e));
        // AlgorithmIdentifier: rsaEncryption (1.2.840.113549.1.1.1) + NULL
        $algId = self::derSeq("\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00");
        $spki  = self::derSeq($algId.self::derBitString($rsaPublicKey));

        return "-----BEGIN PUBLIC KEY-----\n"
            .chunk_split(base64_encode($spki), 64, "\n")
            ."-----END PUBLIC KEY-----\n";
    }

    // ---- minimale DER-encoders ----

    private static function derLen(int $len): string
    {
        if ($len < 0x80) {
            return chr($len);
        }
        $out = '';
        while ($len > 0) {
            $out = chr($len & 0xff).$out;
            $len >>= 8;
        }
        return chr(0x80 | strlen($out)).$out;
    }

    private static function derInt(string $bytes): string
    {
        $bytes = ltrim($bytes, "\x00");
        if ($bytes === '') {
            $bytes = "\x00";
        }
        if (ord($bytes[0]) > 0x7f) {
            $bytes = "\x00".$bytes; // positief teken behouden
        }
        return "\x02".self::derLen(strlen($bytes)).$bytes;
    }

    private static function derSeq(string $content): string
    {
        return "\x30".self::derLen(strlen($content)).$content;
    }

    private static function derBitString(string $content): string
    {
        return "\x03".self::derLen(strlen($content) + 1)."\x00".$content;
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64d(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/'));
    }
}
