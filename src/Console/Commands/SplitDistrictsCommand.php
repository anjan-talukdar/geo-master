<?php

namespace AnjanTalukdar\GeoMaster\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SplitDistrictsCommand extends Command
{
    protected $signature = 'geo-master:split-districts {file : The path to the bulk JSON file} {country : The ISO2 code of the country (e.g., IN)}';
    protected $description = 'Developer utility to split a bulk districts JSON file into the modular state-wise format.';

    public function handle()
    {
        $filePath = $this->argument('file');
        $countryIso = strtoupper($this->argument('country'));

        if (!File::exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return;
        }

        $content = File::get($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error("Invalid JSON format in the file.");
            return;
        }

        $baseDir = __DIR__ . '/../../../database/data/districts/' . $countryIso;
        
        if (!File::exists($baseDir)) {
            File::makeDirectory($baseDir, 0755, true);
        }

        $count = 0;

        foreach ($data as $stateCode => $districts) {
            $formattedDistricts = [];

            foreach ($districts as $district) {
                // If it's just a string, convert to object. If it's already an array, extract or keep.
                if (is_string($district)) {
                    $formattedDistricts[] = ['name' => $district];
                } elseif (is_array($district) && isset($district['name'])) {
                    $formattedDistricts[] = ['name' => $district['name']];
                } else {
                    $formattedDistricts[] = $district; // Fallback
                }
            }

            $outputFile = $baseDir . '/' . strtoupper($stateCode) . '.json';
            File::put($outputFile, json_encode($formattedDistricts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->line("Created: {$countryIso}/" . strtoupper($stateCode) . ".json with " . count($formattedDistricts) . " districts.");
            $count++;
        }

        $this->info("Successfully split {$count} state district files into the modular structure!");
    }
}
