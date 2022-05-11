<?php

namespace Symbiotic\Apps;

use Symbiotic\Packages\PackagesRepositoryInterface;
use Symbiotic\Core\AbstractBootstrap;
use Symbiotic\Routing\AppsRoutesRepository;
use Symbiotic\Core\CoreInterface;


class Bootstrap extends AbstractBootstrap
{

    public function bootstrap(CoreInterface $core): void
    {


        $core->bind(AppConfigInterface::class,AppConfig::class);
        $core->bind(ApplicationInterface::class,Application::class);

        // todo: занимает 3.5 мс при 700 пакетах, можно в файл писать, будет 2.5
        $core->singleton( AppsRepositoryInterface::class, function ($app) {
            $apps_repository = new AppsRepository();
            foreach ($app[PackagesRepositoryInterface::class]->all() as $config) {
                $app_c = isset($config['app']) ? $config['app'] : null;
                if (is_array($app_c)) {
                    $apps_repository->addApp($app_c);
                }
            }

            return $apps_repository;
        }, 'apps');

        /**
         * @used-by  \Symbiotic\Routing\Provider::boot()
         * or
         * @used-by  \Symbiotic\Routing\SettlementsRoutingProvider::register()
         */
        $core['listeners']->add(AppsRoutesRepository::class, function (AppsRoutesRepository $event, AppsRepositoryInterface $appsRepository) {
            foreach ($appsRepository->enabled() as $v) {
                $provider = $v['routing'] ?? null;
                if ($provider) {
                   /* if(!class_exists($provider)) {
                        throw new \Exception('Provider ['.$provider.'] class not found!');
                    }*/
                    $event->append(new $provider($v['id'], $v['controllers_namespace']));
                }
            }
        });


    }
}
