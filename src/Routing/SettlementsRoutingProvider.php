<?php


namespace Dissonance\Routing;


use Dissonance\Container\CachedContainerInterface;
use Dissonance\Container\DIContainerInterface;
use Dissonance\Apps\AppConfigInterface;
use Dissonance\Packages\PackagesRepositoryInterface;


class SettlementsRoutingProvider extends Provider
{
    public function boot(): void
    {
    }

    public function register(): void
    {
        parent::register();


        //TODO: Надо сравнить, кешировать или нет, ориентировочно на 600 пакетов

        if ($this->app instanceof CachedContainerInterface) {
            // $this->app->cached(SettlementsInterface::class);
        }
        if (!$this->app->bound(SettlementsInterface::class)) {
            $this->app->singleton(SettlementsInterface::class, function ($app) {
                $generator = function () use ($app) {
                    $settlements = $app('config::settlements', []);

                    foreach ($settlements as $v) {
                        yield $v;
                    }
                    // TODO: Должна быть опция отключения формирования по ID

                    /**   Перенесено в проксю {@see PackagesSettlements}
                     * foreach ($app[PackagesRepositoryInterface::class]->getIds() as $id) {
                     * yield  [
                     * 'prefix' => $id,
                     * 'router' => $id
                     * ];
                     * yield [
                     * 'prefix' => 'backend/' . $id,
                     * 'router' => 'backend:' . $id
                     * ];
                     * yield [
                     * 'prefix' => 'api/' . $id,
                     * 'router' => 'api:' . $id
                     * ];
                     * }*/

                };
                $factory = $app[SettlementFactory::class];
                $settlements = new Settlements($generator, $factory);
                if ($app('config::packages_settlements', true)) {
                    $settlements = new PackagesSettlements($settlements, $app[PackagesRepositoryInterface::class], $factory);
                }

                return $settlements;
            }, 'settlements');
        }
        $this->app->alias(SettlementsInterface::class, 'settlements');


    }

    protected function registerRouter()
    {
        $this->app->singleton(RouterInterface::class, function (DIContainerInterface $app) {
            return new SettlementsRouter(
                $this->getFactory(),
                $app['settlements']);
        }, 'router');
    }

    protected function getFactoryClass()
    {
        return RouterNamedFactory::class;
    }

    protected function getRouterClass()
    {
        return RouterLazy::class;
    }

    protected function routesLoaderCallback()
    {
        $app = $this->app;
        return function (RouterInterface $router) use ($app) {


            /**
             * @var SettlementsRouter $routing
             * @var RouterInterface|NamedRouterInterface $router
             */
            $router_name = $router->getName();
            // $routing = $app['router'];
            //$routing->selectRouter($router_name);
            //if (!$router->isLoadedRoutes()) {
            /**
             * @var AppRoutingInterface $provider
             */
            $repo = $app[AppsRoutesRepository::class];

            if ($provider = $repo->getByAppId($router_name)) {
                $provider->loadFrontendRoutes($router);
            } else if (preg_match('~^(backend|api):([0-9a-z_\-\.]+)~', $router_name, $m)) {
                $action = $m[1];
                $app_id = $m[2];
                $provider = $repo->getByAppId($app_id);
                if (!$provider) {
                    return;
                }
                if ($action === 'backend') {
                    // TODO: Нужно сделать и прокинуть Auth Middleware!!!
                    $provider->loadBackendRoutes($router);
                } elseif ($action === 'api') {
                    $provider->loadApiRoutes($router);
                }
            } else if ($router_name === 'default') {
                foreach ($app[AppsRoutesRepository::class]->getProviders() as $provider) {
                    $app_id = $provider->getAppId();
                    $router->group(['as' => $app_id, 'app' => $app_id], function ($router) use ($provider) {
                        $provider->loadDefaultRoutes($router);
                    });

                }
            }

            //   }

            // $routing->selectPreviousRouter();

        };
    }

}