<?php

declare(strict_types=1);

namespace Symbiotic\Bootstrap;

use Psr\EventDispatcher\ListenerProviderInterface;
use Symbiotic\Apps\AppsCloningRepository;
use Symbiotic\Apps\AppsRepositoryInterface;
use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Event\CloningListenerProvider;
use Symbiotic\Event\ListenersInterface;


class CloningExtendersBootstrap implements BootstrapInterface
{
    public function bootstrap(DIContainerInterface $core): void
    {
        /**
         *  Extend Modules service
         */
        $core->extend(
            AppsRepositoryInterface::class,
            static function (AppsRepositoryInterface $repository, DIContainerInterface $app) {
                return new AppsCloningRepository(
                    $repository,
                    $app
                );
            }
        );

        /**
         * Extend Event Listeners service
         */
        $core->extend(
            ListenerProviderInterface::class,
            static function (ListenersInterface $listenerProvider) {
                return new CloningListenerProvider($listenerProvider);
            }
        );
    }
}