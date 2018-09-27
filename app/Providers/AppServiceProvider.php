<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app('view')->composer('webapp/layout', function ($view) {
            $action = app('request')->route()->getAction();

            $controller = class_basename($action['controller']);

            list($currentController, $currentAction) = explode('@', $controller);
            $currentControllerShort = str_replace("Controller", "", $currentController);

            $view->with(compact('currentController', 'currentControllerShort', 'currentAction'));
        });

        app('view')->composer('*', function($view) {
            $currentRequest = request()->request;
            $view->with(compact('currentRequest'));
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
