<?php

namespace Symbiotic\Container;

use \Closure;

interface CachedContainerInterface extends DIContainerInterface, \Serializable
{


    /**
     * Разрешает кеширование сервиса в контейнере
     *
     *
     * !! Только сервисы добавленные через метод {@see DIContainerInterface::singleton()}
     * Если есть сервис кеша в контейнере {@see \Psr\SimpleCache\CacheInterface} , то указанный сервис будет добавлен для кешироваиня
     *
     * @param string $abstract - ключ сервиса для кеширования, использовать интерфейс с которым был добавлен сервис
     *
     */
    public function cached(string $abstract, $concrete = null, string $alias = null);

    public function addToCache(string $abstract);
}