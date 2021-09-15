<?php

namespace Symbiotic\SimpleCacheFilesystem;



interface SimpleCacheInterface extends \Psr\SimpleCache\CacheInterface
{
    /**
     * Метод ищет в кеше данные, если не найдет, то выполнит функцию value, щапишет в кеш и вернет данные
     *
     * @param $key
     * @param \Closure $value
     * @param null $ttl
     * @return mixed - Если в кеше будет найден ключ, то вернет из кеша или вернет результат фнукции $value и запишет в кеш
     */
    public function remember($key, \Closure $value, $ttl = null);

}

