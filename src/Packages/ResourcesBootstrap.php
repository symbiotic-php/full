<?php

namespace Symbiotic\Packages;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symbiotic\Core\AbstractBootstrap;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Http\Kernel\PreloadKernelHandler;

class ResourcesBootstrap extends AbstractBootstrap
{
    protected string $cache_key = 'core.resources';

    /**
     * @param CoreInterface $core
     */
    public function bootstrap(CoreInterface $core): void
    {

        $core->singleton(TemplateCompiler::class);
        $res_interface = ResourcesRepositoryInterface::class;
        $core->alias($res_interface, TemplatesRepositoryInterface::class);
        $core->alias($res_interface, AssetsRepositoryInterface::class);

        $core->singleton($res_interface, function () use ($core) {
            /*$cache = $app('cache');
            if ($cache instanceof CacheInterface
                && ($object = $cache->get($this->cache_key))
                && $object instanceof ResourcesRepositoryInterface) {

                return $object;
            }*/
            /**
             * @var ResourcesRepositoryInterface $repository
             * @var PackagesRepositoryInterface $packages_repository
             */
            // $repository = new ResourcesRepository(...$app->getMultiple([TemplateCompiler::class,StreamFactoryInterface::class,PackagesRepositoryInterface::class]));
            $repository = new ResourcesRepository(
                $core[TemplateCompiler::class],
                $core[StreamFactoryInterface::class],
                $core[PackagesRepositoryInterface::class]
            );

            return $repository;

        }, 'resources');

        $core['listeners']->add(PreloadKernelHandler::class, function (PreloadKernelHandler $event) use ($core) {
            $event->prepend(
                new AssetFileMiddleware(
                    $core('config::assets_prefix', 'assets'),
                    $core['resources'],
                    $core[ResponseFactoryInterface::class]
                )
            );
        });
    }
}