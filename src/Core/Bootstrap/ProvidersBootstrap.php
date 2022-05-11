<?php

namespace Symbiotic\Core\Bootstrap;

use Symbiotic\Core\{AbstractBootstrap, ProvidersRepository};
use function _S\config;

class ProvidersBootstrap extends AbstractBootstrap
{
    /**
     * @param \Symbiotic\Container\DIContainerInterface | \Symbiotic\Container\ServiceContainerInterface $core
     */
    public function bootstrap($core): void
    {
        $providers_class = ProvidersRepository::class;
        $this->cached($core, $providers_class);
        /**
         * @var ProvidersRepository $providers_repository
         */
        $providers_repository = $core[$providers_class];
        $providers_repository->load($core, config('providers', []), config('providers_exclude', []));
    }
}