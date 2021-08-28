<?php

namespace Symbiotic\Apps;


use Symbiotic\Packages\PackagesRepositoryInterface;
use Symbiotic\Core\AbstractBootstrap;
use Symbiotic\Routing\AppsRoutesRepository;
use Symbiotic\Core\CoreInterface;


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
         * @used-by  \Symbiotic\Routing\Provider::boot()
         * or
         * @used-by  \Symbiotic\Routing\SettlementsRoutingProvider::register()
         */
        $app['listeners']->add(AppsRoutesRepository::class, function (AppsRoutesRepository $event, AppsRepositoryInterface $appsRepository) {
            foreach ($appsRepository->enabled() as $v) {
                $provider = $v['routing'] ?? null;
                if ($provider) {
                    if(!class_exists($provider)) {
                        throw new \Exception('Provider ['.$provider.'] class not found!');
                    }
                    $event->append(new $provider($v['id'], $v['controllers_namespace']));
                }
            }
        });


    }
}
