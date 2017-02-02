<?php
namespace Martindevnow\Smartshelf;

use Illuminate\Support\ServiceProvider;

class SmartshelfServiceProvider extends ServiceProvider {

    /**
     * Register this Service Provider
     */
    public function register() {

    }

    /**
     * Boot this Service Provider
     */
    public function boot() {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}