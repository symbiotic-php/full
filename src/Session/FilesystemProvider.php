<?php

declare(strict_types=1);

namespace Symbiotic\Session;

use Psr\Container\ContainerInterface;
use Symbiotic\Core\ServiceProvider;
use Symbiotic\Filesystem\FilesystemManagerInterface;
use Symbiotic\Session\Handlers\FilesystemHandler;

class FilesystemProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(SessionManager::class, static function (SessionManager $manager) {
            $manager->addDriver(
                'file',
                static function (array $config, ContainerInterface $container): SessionStorageInterface {
                    /**
                     * @var FilesystemManagerInterface $filesystemManager
                     */
                    $filesystemManager = $container->get(FilesystemManagerInterface::class);
                    $handler = new FilesystemHandler($filesystemManager, $config['path'], $config['minutes']);

                    return new SessionStorage(
                        $handler,// todo: encrypt support
                        $config['name'],
                        $config['namespace'],
                        null
                    );
                }
            );
        });
        /**
         * @todo: session gc_collect
         */
    }
}