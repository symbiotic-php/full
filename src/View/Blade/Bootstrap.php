<?php

namespace Dissonance\View\Blade;


use Dissonance\Core\CoreInterface;
use Dissonance\Packages\TemplateCompiler;

class Bootstrap implements \Dissonance\Core\BootstrapInterface
{

    public function bootstrap(CoreInterface $app): void
    {
        $app->afterResolving(TemplateCompiler::class, function(TemplateCompiler $compiler) {
            $compiler->addCompiler(new Blade());;
        });
    }
}
