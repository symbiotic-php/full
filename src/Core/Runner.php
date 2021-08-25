<?php

namespace Dissonance\Core;

/**
 * Class DummyClass
 * @package DummyNamespace
 * @property CoreInterface | array $app = [
 *  // ДАННЫЕ СЕРВИСЫ БЕРУТСЯ ИЗ КОРНЕВОГО КОНТЕЙНЕРА {@see CoreInterface}
 *   // Сервисы доступные сразу
 *
 *       'config' => new \Dissonance\Config(), {@used-by \_DS\config()}
 *       'events' => new \Dissonance\Event\EventDispatcher(), //{@see \Dissonance\Core\Bootstrap\EventBootstrap::bootstrap()}
 *       'listeners' => new \Dissonance\Events\ListenerProvider(),  //{@see \Dissonance\Core\Bootstrap\EventBootstrap::bootstrap()}
 *
 *   // Сервисы которых может еще не быть, но они доступны сразу после отработки всех бутстраперов
 *
 *       'apps' => new \Dissonance\Appss\AppsRepository(),  //{@see \Dissonance\Apps\Bootstrap::bootstrap()}
 *       'cache' => new \Dissonance\SimpleCache\Cache(),             // может и не быть пакета
 *       'resources' => new \Dissonance\Packages\Resources(),        //{@see \Dissonance\Packages\ResourcesBootstrap::bootstrap()}
 *       'http_factory' => new \Dissonance\Http\PsrHttpFactory(),    //{@see \Dissonance\Http\Bootstrap::bootstrap()}
 *
 *   // Сервисы из провайдеров, доступны после бутстрапа ядра {@see HttpRunner::run(), HttpKernel::bootstrap()}
 *
 *        //  HTTP сервисы, используются в {@see HttpKernel::handle(), RoutingHandler::handle()}
 *       'router'  => new \Dissonance\Routing\Router(),    //{@see \Dissonance\Routing\Provider::registerRouter()}
 *       'request' => new \Dissonance\Http\ServerRequest(),         //{@see  HttpKernel::handle()}
 *       'session'  => new \Dissonance\Session\SessionStorageInterface(), //{@see \Dissonance\Session\SessionStorageNative}
 *       'cookie'  => new \Dissonance\Http\Cookie\CookiesInterface(), //{@see \Dissonance\Http\Cookie\CookiesProvider::register()}
 *       // Доступен только при обработке в контроллерах!!!
 *       'route' => new \Dissonance\Routing\RouteInterface(),           //{@see \Dissonance\Http\Kernel\RouteHandler::handle()}
 * ]
 */
abstract class Runner implements RunnerInterface
{
    /**
     * @var CoreInterface
     */
    protected $app;

    public function __construct(CoreInterface $container)
    {
        $this->app = $container;
    }

}