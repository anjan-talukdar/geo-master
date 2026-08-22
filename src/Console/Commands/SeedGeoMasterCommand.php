<?php

namespace AnjanTalukdar\GeoMaster\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class SeedGeoMasterCommand extends Command
{
    protected $signature = 'geo-master:seed';
    protected $description = 'Seed geographic data from package JSON files into the database.';

    public function handle()
    {
        $this->info('Starting GeoMaster data seeding...');

        $dataPath = __DIR__ . '/../../../database/data';

        if (!File::exists($dataPath)) {
            $this->error('Data path not found: ' . $dataPath);
            return;
        }

        $now = Carbon::now();

        // 1. Seed Countries
        $countriesFile = $dataPath . '/countries.json';
        if (File::exists($countriesFile)) {
            $this->info('Seeding Countries...');
            $countriesData = json_decode(File::get($countriesFile), true);
            $countryInserts = [];
            
            $countryMap = [];
            
            foreach ($countriesData as $country) {
                $countryInserts[] = [
                    'name' => $country['name'],
                    'iso2' => $country['iso2'],
                    'iso3' => $country['iso3'] ?? null,
                    'phone_code' => $country['phone_code'] ?? null,
                    'currency' => $country['currency'] ?? null,
                    'capital' => $country['capital'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            
            DB::table('countries')->upsert($countryInserts, ['iso2'], ['name', 'iso3', 'phone_code', 'currency', 'capital', 'updated_at']);
            
            // Build map
            $dbCountries = DB::table('countries')->get();
            foreach ($dbCountries as $dbC) {
                $countryMap[$dbC->iso2] = $dbC->id;
            }

            // 2. Seed States
            $statesPath = $dataPath . '/states';
            if (File::exists($statesPath)) {
                $this->info('Seeding States...');
                $stateFiles = File::files($statesPath);
                
                $stateMap = []; // [iso2][state_code] => id

                foreach ($stateFiles as $file) {
                    if ($file->getExtension() !== 'json') continue;
                    
                    $iso2 = $file->getFilenameWithoutExtension(); // e.g. "IN"
                    
                    if (!isset($countryMap[$iso2])) {
                        $this->warn("Country ISO2 '{$iso2}' not found in DB. Skipping {$file->getFilename()}");
                        continue;
                    }
                    
                    $countryId = $countryMap[$iso2];
                    $statesData = json_decode(File::get($file->getPathname()), true);
                    $stateInserts = [];

                    foreach ($statesData as $state) {
                        $stateInserts[] = [
                            'country_id' => $countryId,
                            'name' => $state['name'],
                            'state_code' => $state['state_code'] ?? null,
                            'gst_code' => $state['gst_code'] ?? null,
                            'capital' => $state['capital'] ?? null,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                    
                    DB::table('states')->upsert($stateInserts, ['country_id', 'state_code'], ['name', 'gst_code', 'capital', 'updated_at']);
                    
                    // Fetch inserted states to map for districts
                    $dbStates = DB::table('states')->where('country_id', $countryId)->get();
                    foreach ($dbStates as $dbS) {
                        $stateMap[$iso2][$dbS->state_code] = $dbS->id;
                    }
                }

                // 3. Seed Districts
                $districtsPath = $dataPath . '/districts';
                if (File::exists($districtsPath)) {
                    $this->info('Seeding Districts...');
                    $countryDirectories = File::directories($districtsPath);
                    
                    foreach ($countryDirectories as $countryDir) {
                        $iso2 = basename($countryDir); // e.g. "IN"
                        $districtFiles = File::files($countryDir);
                        
                        foreach ($districtFiles as $file) {
                            if ($file->getExtension() !== 'json') continue;
                            
                            $stateCode = $file->getFilenameWithoutExtension(); // e.g. "AS"
                            
                            if (!isset($stateMap[$iso2][$stateCode])) {
                                $this->warn("State '{$iso2}/{$stateCode}' not found in DB. Skipping {$file->getFilename()}");
                                continue;
                            }
                            
                            $stateId = $stateMap[$iso2][$stateCode];
                            $districtsData = json_decode(File::get($file->getPathname()), true);
                            $districtInserts = [];
                            
                            foreach ($districtsData as $district) {
                                $districtInserts[] = [
                                    'state_id' => $stateId,
                                    'name' => $district['name'],
                                    'created_at' => $now,
                                    'updated_at' => $now,
                                ];
                            }
                            
                            DB::table('districts')->upsert($districtInserts, ['state_id', 'name'], ['updated_at']);
                        }
                    }
                }
            }
        } else {
            $this->warn('No countries.json found. Skipping seeding.');
        }

        $this->info('GeoMaster seeding completed successfully.');
    }
}
