<?php

namespace Dissonance\Core;

/**
 * Allowed only in Core container!
 * Interface BootstrapInterface
 * @package Dissonance\Contracts
 */
interface BootstrapInterface
{
    /**
     * @param CoreInterface|\Dissonance\Core\Core | array $app = [
     *   // Сервисы доступные сразу
     *
     *       'config' => new \Dissonance\Config(),
     *       'events' => new \Dissonance\Contracts\Events\Dispatcher(), //{@see \Dissonance\Core\Bootstrap\EventBootstrap::bootstrap()}
     *       'listeners' => new \Dissonance\Events\ListenerProvider(),  //{@see \Dissonance\Core\Bootstrap\EventBootstrap::bootstrap()}
     *
     *   // Сервисы которых может еще не быть, но они доступны сразу после отработки всех бутстраперов
     *
     *       'apps' => new \Dissonance\Contracts\Appss\AppsRepository(),  //{@see \Dissonance\Apps\Bootstrap::bootstrap()}
     *       'cache' => new \Dissonance\SimpleCacheFilesystem\Cache(),             // может и не быть пакета
     *       'resources' => new \Dissonance\Packages\Resources(),        //{@see \Dissonance\Packages\ResourcesBootstrap::bootstrap()}
     *       'http_factory' => new \Dissonance\Http\PsrHttpFactory(),    //{@see \Dissonance\Http\Bootstrap::bootstrap()}
     *
     *   // Сервисы из провайдеров, доступны после бутстрапа ядра {@see HttpRunner::run(), HttpKernel::bootstrap()}
     *        // Http сервисы
     *       'router' => new \Dissonance\Contracts\Routing\Router(),    //{@see \Dissonance\Routing\Provider::registerRouter()}
     *       'request' => new \Dissonance\Http\ServerRequest(),         //{@see \Dissonance\Http\Bootstrap::bootstrap()}
     *       'cookie' => new \Dissonance\Http\Cookie\CookiesInterface(),
     *       // Доступен только при обработке в контроллерах
     *       'route' => new \Dissonance\Http\ServerRequest(),           //{@see \Dissonance\Http\Bootstrap::bootstrap()}
     * ]
     */
    public function bootstrap(CoreInterface $app) : void;
}