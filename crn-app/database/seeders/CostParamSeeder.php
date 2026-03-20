<?php

namespace Database\Seeders;

use App\Models\CostParam;
use Illuminate\Database\Seeder;

class CostParamSeeder extends Seeder
{
    public function run(): void
    {
        $params = [
            ['name' => 'SAC FİYATI', 'key' => 'sac_fiyati', 'value' => 6.5, 'unit' => null],
            ['name' => 'MAŞON FİYATI', 'key' => 'mason_fiyati', 'value' => 1.39, 'unit' => null],
            ['name' => 'TUTACAK FİYATI', 'key' => 'tutacak_fiyati', 'value' => 0.5, 'unit' => null],
        ];
        foreach ($params as $p) {
            CostParam::firstOrCreate(['key' => $p['key']], $p);
        }
    }
}
