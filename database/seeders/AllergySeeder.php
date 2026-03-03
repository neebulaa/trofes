<?php

namespace Database\Seeders;

use App\Models\Allergy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AllergySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = database_path('seeders/data/allergy.csv');

        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable at $filePath");
        }

        $csv = fopen($filePath, 'r');

        // Read header row
        $header = fgetcsv($csv);

        while (($row = fgetcsv($csv)) !== false) {            
            $data = array_combine($header, $row);

            $image_path = null;
            $filename = $data['allergy_name'] . '.png';
            $sourcePath = public_path('assets/allergies/' . $filename);
            
            if (File::exists($sourcePath)) {
                $image_path = 'allergies/' . $filename;
                Storage::disk('public')->put(
                        $image_path,
                        File::get($sourcePath)
                    );
            }

            Allergy::create([
                'allergy_code' => $data['allergy_code'],
                'allergy_name' => $data['allergy_name'],
                'examples' => $data['examples'],
                'image' => $image_path
            ]);
        }

        fclose($csv);
    }
}
