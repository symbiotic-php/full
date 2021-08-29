<?php

namespace Symbiotic\Core\Bootstrap;

use Symbiotic\Core\{AbstractBootstrap, ProvidersRepository};
use function _DS\config;

class ProvidersBootstrap extends AbstractBootstrap
{
    /**
     * @param \Symbiotic\Container\DIContainerInterface | \Symbiotic\Container\ServiceContainerInterface $app
     */
    public function bootstrap($app):void
    {
        $providers_class = ProvidersRepository::class;
        $this->cached($app, $providers_class);
        /**
         * @var ProvidersRepository $providers_repository
         */
        $providers_repository = $app[$providers_class];
        $providers_repository->load($app, config('providers', []), config('providers_exclude', []));
    }
}