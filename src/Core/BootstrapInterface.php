<?php

namespace Symbiotic\Core;

/**
 * Allowed only in Core container!
 * Interface BootstrapInterface
 * @package Symbiotic\Contracts
 */
interface BootstrapInterface
{
    /**
     * @param CoreInterface|\Symbiotic\Core\Core | array $app = [
     *   // Сервисы доступные сразу
     *
     *       'config' => new \Symbiotic\Config(),
     *       'events' => new \Symbiotic\Contracts\Events\Dispatcher(), //{@see \Symbiotic\Core\Bootstrap\EventBootstrap::bootstrap()}
     *       'listeners' => new \Symbiotic\Events\ListenerProvider(),  //{@see \Symbiotic\Core\Bootstrap\EventBootstrap::bootstrap()}
     *
     *   // Сервисы которых может еще не быть, но они доступны сразу после отработки всех бутстраперов
     *
     *       'apps' => new \Symbiotic\Contracts\Appss\AppsRepository(),  //{@see \Symbiotic\Apps\Bootstrap::bootstrap()}
     *       'cache' => new \Symbiotic\SimpleCacheFilesystem\Cache(),             // может и не быть пакета
     *       'resources' => new \Symbiotic\Packages\Resources(),        //{@see \Symbiotic\Packages\ResourcesBootstrap::bootstrap()}
     *       'http_factory' => new \Symbiotic\Http\PsrHttpFactory(),    //{@see \Symbiotic\Http\Bootstrap::bootstrap()}
     *
     *   // Сервисы из провайдеров, доступны после бутстрапа ядра {@see HttpRunner::run(), HttpKernel::bootstrap()}
     *        // Http сервисы
     *       'router' => new \Symbiotic\Contracts\Routing\Router(),    //{@see \Symbiotic\Routing\Provider::registerRouter()}
     *       'request' => new \Symbiotic\Http\ServerRequest(),         //{@see \Symbiotic\Http\Bootstrap::bootstrap()}
     *       'cookie' => new \Symbiotic\Http\Cookie\CookiesInterface(),
     *       // Доступен только при обработке в контроллерах
     *       'route' => new \Symbiotic\Http\ServerRequest(),           //{@see \Symbiotic\Http\Bootstrap::bootstrap()}
     * ]
     */
    public function bootstrap(CoreInterface $app) : void;
}