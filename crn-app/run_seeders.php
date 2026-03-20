<?php

/**
 * Tüm seed'leri çalıştırır (php artisan db:seed ile aynı).
 * Sunucuda konsol yoksa: php run_seeders.php
 * Proje dışındaysanız: LARAVEL_PATH=/path/to/crn-app php run_seeders.php
 */

$laravelPath = getenv('LARAVEL_PATH') ?: __DIR__;

require $laravelPath . '/vendor/autoload.php';
$app = require_once $laravelPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

Config::set('hashing.driver', 'bcrypt');

Artisan::call('db:seed', ['--force' => true]);

echo Artisan::output();

// Seed'deki kullanıcı şifrelerini Bcrypt ile düzelt (giriş hatası olmasın)
$users = [
    ['email' => 'admin@crn.local', 'password' => 'password'],
    ['email' => 'musteri@crn.local', 'password' => 'password'],
];
foreach ($users as $u) {
    $hash = Hash::driver('bcrypt')->make($u['password']);
    DB::table('users')->where('email', $u['email'])->update(['password' => $hash]);
}

echo "OK - Tüm seed'ler tamamlandı.\n";
echo "Admin: admin@crn.local / password\n";
echo "Müşteri: musteri@crn.local / password\n";
