<?php

namespace Symbiotic\Packages;

use Symbiotic\Http\Kernel\PreloadKernelHandler;
use Symbiotic\Http\Middleware\MiddlewaresDispatcher;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ResourcesBootstrap
{
    protected $cache_key = 'core.resources';

    /**
     * @param \Symbiotic\Container\ServiceContainerInterface|\Symbiotic\Core\Core $app
     */
    public function bootstrap($app)
    {

        $app->singleton(TemplateCompiler::class);
        $res_interface = ResourcesRepositoryInterface::class;
        $app->alias($res_interface, TemplatesRepositoryInterface::class);
        $app->alias($res_interface, AssetsRepositoryInterface::class);

        $app->singleton($res_interface, function () use ($app) {
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
                $app[TemplateCompiler::class],
                $app[StreamFactoryInterface::class],
                $app[PackagesRepositoryInterface::class]
            );

            return $repository;

        }, 'resources');

        $app['listeners']->add(PreloadKernelHandler::class, function (PreloadKernelHandler $event) use ($app) {
            $event->prepend(
                new AssetFileMiddleware(
                    $app('config::assets_prefix', 'assets'),
                    $app['resources'],
                    $app[ResponseFactoryInterface::class]
                )
            );

        });
    }
}