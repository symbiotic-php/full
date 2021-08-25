<?php

namespace Dissonance\Core;

use Dissonance\Container\DIContainerInterface;
/**
 * Class ServiceProvider
 * @package Dissonance
 * @property  DIContainerInterface|array $app  = [
 *       'config' => new \Dissonance\Config(),
 *       'router' => new \Dissonance\Contracts\Routing\Router(),
 *       'apps' => new \Dissonance\Contracts\Appss\AppsRepository(),
 *       'events' => new \Dissonance\Contracts\Events\Dispatcher(),
 *       'listeners' => new \Dissonance\Event\ListenerProvider()
 * ]
 */
class  ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var DIContainerInterface| [ 'config' => new \Dissonance\Config() ]
     */
    protected $app = null;

    public function __construct(DIContainerInterface $app)
    {
        $this->app = $app;
    }

    /**
     * @return void
     * @phpcompressor-delete
     */
    public function register():void
    {
    }

    /**
     * @return void
     * @phpcompressor-delete
     */
    public function boot():void
    {
    }

    /**
     * Возвращает массив привязок
     *
     * Вы можете описать данный метод, чтобы массово вернуть фабричные методы для создания для объектов
     * return [
     *      ClassName::class => function($dependencies){return new ClassName();},
     *      TwoClass:class   => function(Config $data){return new TwoClass($data);},
     * ]
     *
     * @return array| \Closure[]
     * @phpcompressor-delete
     */
    public function bindings(): array
    {
        return [];
    }

    /**
     * Возвращает массив привязок
     *
     * Вы можете описать данный метод, чтобы массово вернуть фабричные методы для создания для объектов
     * return [
     *      ClassName::class => function($dependencies){return new ClassName();},
     *      TwoClass:class   => function($data){return new TwoClass($data);},
     * ]
     *
     * @return string[]| \Closure[]
     * @phpcompressor-delete
     */
    public function singletons(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function aliases(): array
    {
        return [];
    }


}