# GeoMaster Package for Laravel

An ejectable scaffolding package for managing global geographic data (Countries, States, Districts, Cities) in Laravel.

## Features

- **Ejectable Scaffold**: Installs models and migrations directly into your host application.
- **Lightweight Storage**: After seating the database, you can simply uninstall the package to save storage space. The JSON data is not published to your repository.
- **Hierarchical Data**: Supports Countries, States (with GST code for India), Districts, and Cities.
- **Extremely Efficient Seeding**: The chunked JSON loading architecture prevents memory exhaustion and runs quickly.

## Installation

```bash
composer require anjan-talukdar/geo-master
```

## Usage

1. **Install Scaffolding**: 
This command publishes the `Country`, `State`, `District`, and `City` models, as well as the necessary migration files into your root project.

```bash
php artisan geo-master:install
```

2. **Run Migrations**:
```bash
php artisan migrate
```

3. **Seed Data**:
This command reads the massive geographic data chunks from the package vendor directory and efficiently populates your database.
```bash
php artisan geo-master:seed
```

4. **Eject (Optional)**:
Once you have the data seeded and the models in your app, you probably don't need the massive raw JSON source files anymore. You can uninstall the package to keep your project size small!
```bash
composer remove anjan-talukdar/geo-master
```

## Maintenance

To update the data in the future, simply re-require the package, run `php artisan geo-master:seed` again, and optionally remove it.
