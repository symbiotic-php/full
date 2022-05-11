<?php

namespace Symbiotic\Filesystem;

use Symbiotic\Core\BootstrapInterface;
use Symbiotic\Core\CoreInterface;
use function _S\listen;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap(CoreInterface $core): void
    {
        $core->singleton(FilesystemManagerInterface::class, function ($app) {
            return new FilesystemManager($app);
        },'files');
    }
}