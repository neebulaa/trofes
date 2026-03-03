<?php

namespace Database\Seeders;

use App\Models\DietaryPreference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

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

            $image_path = null;
            $filename = $data['diet_name'] . '.png';
            $sourcePath = public_path('assets/dietary_preferences/' . $filename);
            
            if (File::exists($sourcePath)) {
                $image_path = 'dietary_preferences/' . $filename;
                Storage::disk('public')->put(
                        $image_path,
                        File::get($sourcePath)
                    );
            }

            DietaryPreference::create([
                'diet_code' => $data['diet_code'],
                'diet_name' => $data['diet_name'],
                'diet_desc' => $data['diet_desc'],
                'image' => $image_path
            ]);
        }
    }
}
