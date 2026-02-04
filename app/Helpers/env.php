<?php

declare(strict_types=1);

/**
 * env() bootstrap'ta tanımlanır; bu dosya yalnızca IDE/başka yerden
 * kullanılırsa diye yedek tanım (bootstrap zaten env() yüklüyor).
 */
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === '') {
            return $default;
        }
        return $value;
    }
}
