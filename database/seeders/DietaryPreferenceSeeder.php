<?php

namespace Database\Seeders;

use App\Models\DietaryPreference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DietaryPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/dietary_preference.csv');
        $csvData = array_map('str_getcsv', file($filePath));

        // Remove header row
        $header = array_shift($csvData);

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            DietaryPreference::create([
                'diet_code' => $data['diet_code'],
                'diet_name' => $data['diet_name']
            ]);
        }
    }
}
