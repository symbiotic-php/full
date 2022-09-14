<?php

declare(strict_types=1);

namespace Symbiotic\Routing;

use Symbiotic\Container\DIContainerInterface;


class SettlementsRoutingProvider extends Provider
{

    public function register(): void
    {
        parent::register();


        $core = $this->app;
        //TODO: It is necessary to compare, cache or not, approximately 600 packets

        /* if ($core instanceof CachedContainerInterface) {
             $this->app->cached(SettlementsInterface::class);
         }*/
        if (!$core->bound(SettlementsInterface::class)) {
            $core->singleton(
                SettlementsInterface::class,
                static function ($app) {
                    $factory = $app[SettlementFactory::class];

                    $settlements = new Settlements($factory, $app('config::settlements', []));
                    if ($app('config::packages_settlements', true)) {
                        $settlements = new PackagesSettlements(
                            $settlements,
                            $app[AppsRoutesRepository::class],
                            $factory,
                            \trim($app('config::backend_prefix', 'backend'), '/')
                        );
                    }

                    return $settlements;
                },
                'settlements'
            );
        }
        $core->alias(SettlementsInterface::class, 'settlements');
    }

    protected function registerRouter()
    {
        $this->app->singleton(RouterInterface::class, function (DIContainerInterface $app) {
            return new SettlementsRouter(
                $this->getFactory(),
                $app['settlements'],
                $app[AppsRoutesRepository::class]
            );
        },                    'router');
    }

    protected function getFactoryClass(): string
    {
        return RouterNamedFactory::class;
    }

    protected function getRouterClass(): string
    {
        return RouterLazy::class;
    }

    protected function routesLoaderCallback(): \Closure
    {
        $app = $this->app;
        return function (RouterInterface $router) use ($app) {
            /**
             * @var SettlementsRouter                    $routing
             * @var RouterInterface|NamedRouterInterface $router
             */
            $router_name = $router->getName();

            /**
             * @var AppRoutingInterface  $provider
             * @var AppsRoutesRepository $repo
             */
            $repo = $app[AppsRoutesRepository::class];

            if (preg_match('~^(backend|api):([\da-z_@\-\.]+)~', $router_name, $m)) {
                $global_router = $m[1];
                $app_id = $m[2];
                $provider = $repo->getByAppId($app_id);
                if (!$provider) {
                    return;
                }
                if ($global_router === 'backend') {
                    $provider->loadBackendRoutes($router);
                } elseif ($global_router === 'api') {
                    $provider->loadApiRoutes($router);
                }
            } else {
                if ($router_name === 'default') {
                    // We load the default router for all applications at once
                    foreach ($repo->getProviders() as $provider) {
                        $app_id = $provider->getAppId();
                        /**
                         * default:...
                         * the prefix of the global router is added automatically when loading above
                         * @see SettlementsRouter::getNamedRoutes()
                         */
                        $router->group(['as' => $app_id . '::', 'app' => $app_id],
                            function (RouterInterface $router) use ($provider) {
                                $provider->loadDefaultRoutes($router);
                            });
                    }
                } else {
                    if ($provider = $repo->getByAppId($router_name)) {
                        $provider->loadFrontendRoutes($router);
                    }
                }
            }
        };
    }
}