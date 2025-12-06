<?php

namespace Database\Seeders;

use App\Models\Guide;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class GuideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Guide::factory(10)->create();
    }
}
