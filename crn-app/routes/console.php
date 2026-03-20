<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('crn:test-admin', function () {
    Role::firstOrCreate(['name' => 'admin'], ['label' => 'Yönetici']);

    $user = User::updateOrCreate(
        ['email' => 'test@crn.local'],
        ['name' => 'Test Admin', 'password' => 'password123']
    );

    $adminRole = Role::where('name', 'admin')->first();
    $user->roles()->syncWithoutDetaching([$adminRole->id]);

    $this->info('Test admin oluşturuldu.');
    $this->info('E-posta: test@crn.local');
    $this->info('Şifre: password123');
    $this->info('Giriş: http://localhost:8000/admin/login');
})->purpose('Admin panele giriş için test kullanıcısı oluşturur');
