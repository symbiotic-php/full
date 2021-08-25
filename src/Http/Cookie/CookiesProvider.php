<?php

namespace Dissonance\Http\Cookie;

use Dissonance\Core\CoreInterface;
use Dissonance\Http\Middleware\MiddlewaresDispatcher;
use Dissonance\Core\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;


class CookiesProvider extends ServiceProvider
{
    public function register(): void
    {
        $app = $this->app;
        $app->singleton(CookiesInterface::class, function (CoreInterface $app) {
            $request = $app['request'];
            $expires = $app('config::cookie_expires', 3600 * 24 * 365);
            $cookies = $this->factoryCookiesClass();
            if ($request instanceof ServerRequestInterface) {
                $cookies->setDefaults(
                    $request->getUri()->getHost(),
                    '/',
                    $expires,
                    $request->getUri()->getScheme() === 'https'
                );
            } else {
                $cookies->setDefaults($app['config::default_host'], '/', $expires);
            }
            return $cookies;
        }, 'cookie');

        $app['listeners']->add(MiddlewaresDispatcher::class, function ($event) use ($app) {
            /** @var MiddlewaresDispatcher $event */
            $event->prependToGroup(MiddlewaresDispatcher::GROUP_GLOBAL, CookiesMiddleware::class);

        });
    }

    protected function factoryCookiesClass(): CookiesInterface
    {
        return new Cookies();
    }
}