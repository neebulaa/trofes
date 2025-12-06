<?php

namespace Database\Seeders;

use App\Models\Allergy;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AllergySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/allergy.csv');
        $csvData = array_map('str_getcsv', file($filePath));

        // Remove header row
        $header = array_shift($csvData);

        foreach ($csvData as $row) {
            $data = array_combine($header, $row);

            Allergy::create([
                'allergy_code' => $data['allergy_code'],
                'allergy_name' => $data['allergy_name']
            ]);
        }
    }
}
