<?php


namespace Symbiotic\Session;

use Symbiotic\Core\ServiceProvider;

class NativeProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SessionStorageInterface::class, function ($app) {
            return new SessionStorageNative('5a8309dedb810d2322b6024d536832ba'); //md5(symbiotic)
        }, 'session');
    }
}