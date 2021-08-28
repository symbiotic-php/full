<?php

namespace Symbiotic\Core;

use Symbiotic\Container\DIContainerInterface;
/**
 * Class ServiceProvider
 * @package Symbiotic
 * @property  DIContainerInterface|array $app  = [
 *       'config' => new \Symbiotic\Config(),
 *       'router' => new \Symbiotic\Contracts\Routing\Router(),
 *       'apps' => new \Symbiotic\Contracts\Appss\AppsRepository(),
 *       'events' => new \Symbiotic\Contracts\Events\Dispatcher(),
 *       'listeners' => new \Symbiotic\Event\ListenerProvider()
 * ]
 */
class  ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var DIContainerInterface| [ 'config' => new \Symbiotic\Config() ]
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