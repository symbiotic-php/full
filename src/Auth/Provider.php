<?php

namespace Symbiotic\Auth;

use Symbiotic\Auth\Authenticator\MultiAuthenticator;
use Symbiotic\Auth\Storage\AuthSessionStorage;
use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Core\ServiceProvider;
use Symbiotic\Event\ListenersInterface;
use Symbiotic\Http\Kernel\RouteMiddlewares;
use Symbiotic\Session\SessionStorageInterface;


class Provider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthStorageInterface::class, function (DIContainerInterface $app) {
            return new AuthSessionStorage($app[SessionStorageInterface::class]);
        });

        $this->app->singleton(MultiAuthenticator::class);

        $this->app->bind(AuthServiceInterface::class, function (DIContainerInterface $app) {
            return new AuthService($app[AuthStorageInterface::class], $app[MultiAuthenticator::class]);
        }, 'auth');

        $this->app->get(ListenersInterface::class)->add(RouteMiddlewares::class, function (RouteMiddlewares $event) {
            $event->prepend(new AuthMiddleware($this->app, $this->app['route']));
        });
    }

}