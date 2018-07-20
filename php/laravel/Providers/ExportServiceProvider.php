<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ExportServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('export.csv', function ($app) {
            $csvService = new \App\Services\Export\CsvService();

            return $csvService;
        });
    }
}
