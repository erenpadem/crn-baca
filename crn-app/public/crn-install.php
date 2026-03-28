<?php

/**
 * Tek seferlik web kurulumu: migrate + db:seed (Filament admin dahil).
 * CLI yoksa tarayıcıdan bir kez çalıştırın, sonra bu dosyayı SİLİN.
 *
 * Gerekli .env:
 *   CRN_INSTALL_KEY=güçlü-bir-parola
 * İsteğe bağlı (ilk admin şifresini özelleştirmek için):
 *   CRN_ADMIN_EMAIL=admin@site.com
 *   CRN_ADMIN_PASSWORD=güvenli-parola
 *   CRN_ADMIN_NAME=Yönetici
 *
 * Çağrı: https://alanadiniz.com/crn-install.php?key=CRN_INSTALL_KEY_DEĞERİ
 */

declare(strict_types=1);

define('LARAVEL_START', microtime(true));

header('Content-Type: text/plain; charset=utf-8');

$laravelBase = require __DIR__.'/laravel-base.php';

$lockFile = $laravelBase.'/storage/framework/crn_install_completed';

if (is_file($lockFile)) {
    http_response_code(403);
    exit("Kurulum zaten tamamlanmış (lock: storage/framework/crn_install_completed).\n".
        "Yeniden çalıştırmak için bu dosyayı silin veya crn-install.php dosyasını kaldırın.\n");
}

require $laravelBase.'/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once $laravelBase.'/bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$installKey = (string) env('CRN_INSTALL_KEY', '');
$requestKey = (string) ($_GET['key'] ?? '');

if ($installKey === '' || ! hash_equals($installKey, $requestKey)) {
    http_response_code(403);
    exit("Geçersiz veya eksik anahtar. .env içinde CRN_INSTALL_KEY tanımlayın ve ?key=... ile çağırın.\n");
}

try {
    if (empty(config('app.key'))) {
        \Illuminate\Support\Facades\Artisan::call('key:generate', ['--force' => true]);
    }

    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "migrate: tamam\n";

    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    echo "db:seed: tamam (roller, örnek admin/müşteri, maliyet parametreleri)\n";

    $email = env('CRN_ADMIN_EMAIL');
    $password = env('CRN_ADMIN_PASSWORD');
    if (is_string($email) && $email !== '' && is_string($password) && $password !== '') {
        $adminRole = \App\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $user = \App\Models\User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => (string) env('CRN_ADMIN_NAME', 'Admin'),
                    'password' => $password,
                ]
            );
            if (! $user->roles()->where('name', 'admin')->exists()) {
                $user->roles()->attach($adminRole->id);
            }
            echo "Özel admin kullanıcısı güncellendi: {$email}\n";
        }
    }

    touch($lockFile);
    echo "\nBitti. Güvenlik için public/crn-install.php dosyasını sunucudan SİLİN.\n";
    echo "Varsayılan seed hesapları: admin@crn.local / password (admin panel)\n";
} catch (\Throwable $e) {
    http_response_code(500);
    echo "Hata: ".$e->getMessage()."\n";
    if (config('app.debug')) {
        echo "\n".$e->getTraceAsString();
    }
}
