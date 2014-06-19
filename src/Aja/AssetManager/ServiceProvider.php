<?php namespace Aja\AssetManager;

use Aja\AssetManager\Commands\AjaAssetsCommand;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        $this->package('dustingraham/asset-manager');
        
        $this->commands('command.aja.asset');
    }
    
    public function register()
    {
        $this->app['asset-manager-asset'] = $this->app->share(function($app)
        {
            return new Asset();
        });
        
        $this->app['command.aja.asset'] = $this->app->share(function()
        {
            return new AjaAssetsCommand();
        });
    }
}
