<?php

namespace Dissonance\Apps;


use Dissonance\Packages\PackagesRepositoryInterface;
use Dissonance\Core\AbstractBootstrap;
use Dissonance\Routing\AppsRoutesRepository;
use Dissonance\Core\CoreInterface;


class Bootstrap extends AbstractBootstrap
{

    public function bootstrap(CoreInterface $app): void
    {

        $this->cached($app, AppsRepositoryInterface::class, function ($app) {
            $apps_repository = new AppsRepository();
            foreach ($app[PackagesRepositoryInterface::class]->getPackages() as $config) {
                $app = isset($config['app']) ? $config['app'] : null;
                if (is_array($app)) {
                    $apps_repository->addApp($app);
                }
            }
            return $apps_repository;
        }, 'apps');

        /**
         * @used-by  \Dissonance\Routing\Provider::boot()
         * or
         * @used-by  \Dissonance\Routing\SettlementsRoutingProvider::register()
         */
        $app['listeners']->add(AppsRoutesRepository::class, function (AppsRoutesRepository $event, AppsRepositoryInterface $appsRepository) {
            foreach ($appsRepository->enabled() as $v) {
                $provider = $v['routing'] ?? null;
                if ($provider && class_exists($provider)) {
                    $event->append(new $provider($v['id'], $v['controllers_namespace']));
                }
            }
        });


    }
}
