<?php

namespace Database\Seeders;

use App\Models\Dealer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RoleSeeder::class, CostParamSeeder::class]);

        $adminRole = Role::where('name', 'admin')->first();
        $musteriRole = Role::where('name', 'musteri')->first();

        $dealer = Dealer::firstOrCreate(
            ['firma_no' => 'F-1'],
            ['unvan' => 'Örnek Bayi', 'il_ilce' => 'İstanbul']
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@crn.local'],
            ['name' => 'Admin', 'password' => bcrypt('password')]
        );
        if (!$admin->roles()->where('name', 'admin')->exists()) {
            $admin->roles()->attach($adminRole);
        }

        $musteri = User::firstOrCreate(
            ['email' => 'musteri@crn.local'],
            ['name' => 'Müşteri', 'password' => bcrypt('password'), 'dealer_id' => $dealer->id]
        );
        if (!$musteri->roles()->where('name', 'musteri')->exists()) {
            $musteri->roles()->attach($musteriRole);
        }
    }
}
