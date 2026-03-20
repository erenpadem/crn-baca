<?php

/**
 * Sunucuda tek seferlik admin kullanıcı oluşturur (şifre Bcrypt).
 * Kullanım: php create_admin.php
 * Proje dışındaysanız: LARAVEL_PATH=/path/to/crn-app php create_admin.php
 */

$laravelPath = getenv('LARAVEL_PATH') ?: __DIR__;

require $laravelPath . '/vendor/autoload.php';
$app = require_once $laravelPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

Config::set('hashing.driver', 'bcrypt');

$email = 'admin@crnbaca.com';
$password = 'secret123';
$name = 'Admin';

$adminRole = Role::firstOrCreate(
    ['name' => 'admin'],
    ['label' => 'Yönetici']
);

// Bcrypt ile hashle; model cast'ı atlamak için şifreyi doğrudan DB'ye yazıyoruz
$hashedPassword = Hash::driver('bcrypt')->make($password);

$user = User::firstOrCreate(
    ['email' => $email],
    ['name' => $name, 'password' => $hashedPassword]
);

// Model 'hashed' cast bazen farklı driver kullanır; kesin Bcrypt için doğrudan güncelle
DB::table('users')->where('id', $user->id)->update(['password' => $hashedPassword]);

if (!$user->roles()->where('name', 'admin')->exists()) {
    $user->roles()->attach($adminRole);
}

echo "OK - Admin kullanıcı hazır: {$email} / {$password}\n";
echo "Giriş: /admin\n";
