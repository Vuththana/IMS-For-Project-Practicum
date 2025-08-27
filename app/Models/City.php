<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class City extends Model
{
    use Sushi;

    protected $rows;

    public function getRows()
    {
        $filePath = base_path('resources/data/cities.csv'); // Updated path

        // Check if the file exists
        if (!file_exists($filePath)) {
            throw new \Exception("CSV file not found at: " . $filePath);
        }

        $csv = array_map('str_getcsv', file($filePath));
        $header = array_shift($csv);

        return array_map(fn($row) => array_combine($header, $row), $csv);
    }
    public static function getCityOptions(?string $countryId, ?string $stateId): array
    {
        return self::where('country_id', $countryId)
            ->when($stateId, fn($query) => $query->where('state_id', $stateId))
            ->pluck('name', 'id')
            ->toArray();
    }
    
}
