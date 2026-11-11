<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsSalonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salons = json_decode(file_get_contents(__DIR__.'/usa_salons.json'), true, flags: JSON_THROW_ON_ERROR);

        foreach ($salons as $index => $salon) {
            $website = $salon['website'];

            if ($website && ! Str::startsWith($website, ['http://', 'https://'])) {
                $website = 'https://'.$website;
            }

            Location::query()->firstOrCreate(
                ['source_key' => sha1(Str::lower(trim($salon['name'])).'|'.Str::lower(trim($salon['address'])))],
                [
                    'slug' => Str::slug($salon['name'].' '.$salon['city']),
                    'company' => $salon['name'],
                    'state' => $salon['state'],
                    'city' => $salon['city'],
                    'address' => $salon['address'],
                    'website' => $website,
                    'phones' => $salon['phone'] ? [$salon['phone']] : [],
                    'email' => $salon['email'],
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
