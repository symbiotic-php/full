<?php

namespace Symbiotic\Container;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Trait DeepGetter
 * @package Symbiotic\Container
 */
trait DeepGetterTrait /* implements ContainerInterface*/
{

    private string $deep_delimiter = '::';

    /**
     * Менее строгий метод получения данных из контейнера, если ключ не существует вернет NULL по умолчанию
     *
     * @param $key
     * @param null $default
     * @return mixed
     *
     */
    public function __invoke($key, $default = null)
    {
        return $this->get($key, $default);
    }

    /**
     * @param string $key - Возможно использование доступа внутри объекта через точку ,
     * если объект использует {@see \ArrayAccess,\Psr\Container\ContainerInterface}
     * Например: 'config::providers' вернет массив провайдеров из объекта \Symbiotic\Config
     *
     * @return mixed
     *
     * @throws NotFoundExceptionInterface|BindingResolutionException|\Exception
     *
     */
    public function get(string $key)
    {
        /**
         * @var DIContainerInterface|DeepGetterTrait $this
         */
        $key = (false === \strpos($key, $this->deep_delimiter)) ? $key : \explode($this->deep_delimiter, $key);
        try {
            if (\is_array($key)) {
                $c = $key[0];
                $k = $key[1];
                $service = $this->make($c);
                if (\is_array($service)) {
                    if (isset($service[$k]) || \array_key_exists($k, $service)) {// isset 4x fast
                        return $service[$k];
                    }
                } elseif ($service instanceof ContainerInterface || $service instanceof BaseContainerInterface) {
                    if ($service->has($k)) {
                        return $service->get($k);
                    }
                } elseif ($service instanceof \ArrayAccess && $service->offsetExists($k)) {
                    return $service->offsetGet($k);
                }
                throw new NotFoundException($k, $service);
            }
            try {
                return $this->make($key);
            } catch (\Exception $e) {
                if (!$this->has($key) || ($this->has($key) && get_class($e) === '\Exception')) {
                    throw new NotFoundException($key, $this, 4004, $e);
                }
            }

        } catch (ContainerException $e) {
            if ($e instanceof NotFoundExceptionInterface && \func_num_args() === 2) {
                return \func_get_arg(1);
            }
            throw $e;
        }
    }
}
