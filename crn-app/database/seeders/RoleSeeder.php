<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'label' => 'Yönetici'],
            ['name' => 'satis', 'label' => 'Satış'],
            ['name' => 'musteri', 'label' => 'Müşteri'],
            ['name' => 'bayi', 'label' => 'Bayi'],
            ['name' => 'imalathane', 'label' => 'İmalathane'],
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r['name']], $r);
        }
    }
}
