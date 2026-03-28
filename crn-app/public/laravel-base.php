<?php

/**
 * Laravel kök dizinini döndürür (vendor/ ve bootstrap/ burada olmalı).
 *
 * Varsayılan: Bu dosya public/ içindeyse bir üst klasör = Laravel kökü.
 * Paylaşımlı hosting: public_html yalnızca public içeriğiyse, bu klasöre
 * laravel-root.php kopyalayın (örnek: laravel-root.php.example).
 */
if (is_file(__DIR__.'/laravel-root.php')) {
    $base = require __DIR__.'/laravel-root.php';
    if (! is_string($base) || $base === '' || ! is_file($base.'/bootstrap/app.php')) {
        http_response_code(500);
        exit('laravel-root.php geçersiz: Laravel kökünde bootstrap/app.php bulunamadı.');
    }

    return $base;
}

return dirname(__DIR__);
