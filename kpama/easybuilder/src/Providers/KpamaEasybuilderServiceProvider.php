<?php

namespace Kpama\Easybuilder\Providers;

use Illuminate\Support\ServiceProvider;
use Kpama\Easybuilder\Command\KpamaEasybuilderCommand;

class KpamaEasybuilderServiceProvider extends ServiceProvider
{


    public function register()
    {
         
        $this->mergeConfigFrom($this->getPath('config/config.php'),'kpamaeasybuilder');
    }

    public function boot()
    {
        $this->loadRoutesFrom($this->getPath('routes/routes.php'));
        $this->loadMigrationsFrom($this->getPath('database/migrations'));
        $this->loadTranslationsFrom($this->getPath('resources/lang'),'kpamaeasybuilder');
        $this->loadViewsFrom($this->getPath('resources/views'),'kpamaeasybuilder');

        // publish
        $this->publishes([
            $this->getPath('resources/lang') => resource_path('lang/kpama/easybuilder'),
            $this->getPath('resources/views') => resource_path('views/kpama/easybuilder')
        ]);

        $this->publishes([
            $this->getPath('public') => public_path('kpamaeasybuilder')
        ],'public');

        // other
        if($this->app->runningInConsole()){
            $this->commands([
                KpamaEasybuilderCommand::class
            ]);
        }

    }

    protected function getPath(string $append = null): string
    {
        return dirname(__DIR__) .'/'.$append;
    }
}
