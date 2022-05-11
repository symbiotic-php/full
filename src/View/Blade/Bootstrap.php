<?php

namespace Symbiotic\View\Blade;


use Symbiotic\Core\CoreInterface;
use Symbiotic\Packages\TemplateCompiler;

class Bootstrap implements \Symbiotic\Core\BootstrapInterface
{

    public function bootstrap(CoreInterface $core): void
    {
        $core->afterResolving(TemplateCompiler::class, function(TemplateCompiler $compiler) {
            $compiler->addCompiler(new Blade());;
        });
    }
}
