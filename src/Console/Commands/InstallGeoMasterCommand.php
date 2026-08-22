<?php

namespace AnjanTalukdar\GeoMaster\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallGeoMasterCommand extends Command
{
    protected $signature = 'geo-master:install';
    protected $description = 'Install GeoMaster scaffolding (models and migrations) into the host application.';

    public function handle()
    {
        $this->info('Installing GeoMaster Scaffolding...');

        // 1. Publish Models
        $this->publishModels();

        // 2. Publish Migrations
        $this->publishMigrations();

        $this->info('GeoMaster scaffolding installed successfully.');
        $this->info('Run `php artisan migrate` and then `php artisan geo-master:seed`');
    }

    protected function publishModels()
    {
        $modelsPath = app_path('Models');
        if (!File::exists($modelsPath)) {
            File::makeDirectory($modelsPath, 0755, true);
        }

        $models = ['Country', 'State', 'District', 'City'];
        
        foreach ($models as $model) {
            $stub = __DIR__ . '/../../Stubs/Models/' . $model . '.stub';
            $dest = $modelsPath . '/' . $model . '.php';

            if (!File::exists($dest)) {
                File::copy($stub, $dest);
                $this->line("Published: App\\Models\\{$model}");
            } else {
                $this->warn("Model {$model} already exists. Skipping.");
            }
        }
    }

    protected function publishMigrations()
    {
        $migrationsPath = database_path('migrations');
        $stub = __DIR__ . '/../../Stubs/Migrations/create_geo_master_tables.php.stub';
        
        // Check if already published
        $files = File::glob($migrationsPath . '/*_create_geo_master_tables.php');
        if (count($files) > 0) {
            $this->warn("Migration already published. Skipping.");
            return;
        }

        // Use a very early timestamp so these master tables run before everything else
        $timestamp = '0000_00_00_000000';
        $dest = $migrationsPath . '/' . $timestamp . '_create_geo_master_tables.php';

        File::copy($stub, $dest);
        $this->line("Published Migration: {$timestamp}_create_geo_master_tables.php");
    }
}
