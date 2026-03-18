<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'pcs'],
            ['name' => 'tablet'],
            ['name' => 'kapsul'],
            ['name' => 'botol'],
            ['name' => 'box'],
            ['name' => 'strip'],
            ['name' => 'tube'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
