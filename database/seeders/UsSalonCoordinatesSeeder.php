<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UsSalonCoordinatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coordinates = json_decode(file_get_contents(__DIR__.'/usa_salon_coordinates.json'), true, flags: JSON_THROW_ON_ERROR);
        $sourceKeys = array_column($coordinates, 'source_key');

        if (count($coordinates) !== 54 || count(array_unique($sourceKeys)) !== 54) {
            throw new RuntimeException('Expected 54 unique salon coordinates. No locations were changed.');
        }

        foreach ($coordinates as $coordinate) {
            if (! is_numeric($coordinate['latitude']) || ! is_numeric($coordinate['longitude'])
                || $coordinate['latitude'] < -90 || $coordinate['latitude'] > 90
                || $coordinate['longitude'] < -180 || $coordinate['longitude'] > 180) {
                throw new RuntimeException('Invalid salon coordinates. No locations were changed.');
            }
        }

        $updated = DB::transaction(function () use ($coordinates, $sourceKeys): int {
            $salons = Location::query()->whereIn('source_key', $sourceKeys)->lockForUpdate()->get()->keyBy('source_key');
            $missing = array_diff($sourceKeys, $salons->keys()->all());

            if ($missing !== []) {
                throw new RuntimeException('Missing salon source keys: '.implode(', ', $missing).'. No locations were changed.');
            }

            $updated = 0;

            foreach ($coordinates as $coordinate) {
                $salon = $salons->get($coordinate['source_key']);
                $salon->fill([
                    'latitude' => $coordinate['latitude'],
                    'longitude' => $coordinate['longitude'],
                ]);

                if ($salon->isDirty(['latitude', 'longitude'])) {
                    $salon->save();
                    $updated++;
                }
            }

            return $updated;
        });

        $this->command?->info("Salon coordinates: {$updated} updated; ".(54 - $updated).' unchanged.');
    }
}
