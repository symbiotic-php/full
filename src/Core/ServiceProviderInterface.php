<?php

namespace Symbiotic\Core;
/**
 * Interface ServiceProvider
 * @package Symbiotic\Container
 * @property  array $app  = [
 *       'config' => new \Symbiotic\Config(),
 *       'router' => new \Symbiotic\Contracts\Routing\Router(),
 *       'apps' => new \Symbiotic\Contracts\Appss\AppsRepository(),
 *
 *
 *
 * ]
 */
interface ServiceProviderInterface
{

    /**
     * @return void
     * @phpcompressor-delete
     */
    public function register(): void;

    /**
     * @return void
     */
    public function boot(): void;

    /**
     * Возвращает массив привязок
     *
     * Вы можете описать данный метод, чтобы массово вернуть фабричные методы для создания для объектов
     * return [
     *      ClassName::class => function($dependencies){return new ClassName();},
     *      SecondClass:class   => function($data){return new SecondClass($data);},
     * ]
     *
     * @return string[]| \Closure[]
     * @phpcompressor-delete
     */
    public function bindings(): array;

    /**
     * Возвращает массив привязок
     *
     * Вы можете описать данный метод, чтобы массово вернуть фабричные методы для создания для объектов
     * return [
     *      ClassName::class => function($dependencies){return new ClassName();},
     *      TwoClass:class   => function($data){return new TwoClass($data);},
     *      TwoInterface:class   => '\\Data\\TwoClass',
     * ]
     *
     * @return string[]| \Closure[]
     * @phpcompressor-delete
     */
    public function singletons(): array;

    /**
     * Возвращает массив илуасов
     *
     * Вы можете описать данный метод, чтобы массово вернуть фабричные методы для создания для объектов
     * return [
     *      ClassName::class => 'class_alias,
     *      TwoClass:class   => 'class_alias_2',
     * ]
     *
     * @return string[]
     * @phpcompressor-delete
     */
    public function aliases(): array;
}