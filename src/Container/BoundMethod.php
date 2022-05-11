<?php

namespace Symbiotic\Container;

use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;

class BoundMethod
{
    /**
     * Call the given \Closure or className@methodName and inject its dependencies.
     *
     * @param DIContainerInterface $container
     * @param callable|string $callback ????array
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     *
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     */
    public static function call($container, $callback, array $parameters = [], string|null $defaultMethod = null)
    {
        if (static::isCallableWithAtSign($callback) || $defaultMethod) {
            return static::callClass($container, $callback, $parameters, $defaultMethod);
        }

        return static::callBoundMethod($container, $callback, function () use ($container, $callback, $parameters) {
            return call_user_func_array(
                $callback, static::getMethodDependencies($container, $callback, $parameters)
            );
        });
    }

    /**
     * Determine if the given string is in Class@method syntax.
     *
     * @param mixed $callback
     * @return bool
     */
    public static function isCallableWithAtSign($callback)
    {
        return is_string($callback) && strpos($callback, '@') !== false;
    }

    /**
     * Call a string reference to a class using Class@method syntax.
     *
     * @param DIContainerInterface $container
     * @param string $target
     * @param array $parameters
     * @param string|null $defaultMethod
     * @return mixed
     *
     * @throws \InvalidArgumentException|\ReflectionException
     */
    protected static function callClass($container, $target, array $parameters = [], $defaultMethod = null)
    {
        $segments = explode('@', $target);
        // We will assume an @ sign is used to delimit the class name from the method
        // name. We will split on this @ sign and then build a callable array that
        // we can pass right back into the "call" method for dependency binding.
        $method = count($segments) === 2
            ? $segments[1] : $defaultMethod;

        if (null === $method) {
            throw new InvalidArgumentException('Method not provided.');
        }

        return static::call(
            $container, [$container->make($segments[0]), $method], $parameters
        );
    }

    /**
     * Call a method that has been bound to the container.
     *
     * @param DIContainerInterface|MethodBindingsTrait $container
     * @param \Closure|array $callback
     * @param mixed $default
     * @return mixed
     */
    protected static function callBoundMethod(DIContainerInterface $container, array|\Closure $callback, $default)
    {
        if (!is_array($callback)) {
            return $default instanceof \Closure ? $default() : $default;
        }

        // Here we need to turn the array callable into a Class@method string we can use to
        // examine the container and see if there are any method bindings for this given
        // method. If there are, we can call this method binding callback immediately.
        $method = static::normalizeMethod($callback);

        if ($container->hasMethodBinding($method)) {
            return $container->callMethodBinding($method, $callback[0]);
        }

        return $default instanceof \Closure ? $default() : $default;
    }

    /**
     * Normalize the given callback into a Class@method string.
     *
     * @param array $callback
     * @return string
     */
    protected static function normalizeMethod(array $callback)
    {
        $class = is_string($callback[0]) ? $callback[0] : get_class($callback[0]);

        return "{$class}@{$callback[1]}";
    }

    /**
     * Get all dependencies for a given method.
     *
     * @param DIContainerInterface $container
     * @param callable|string $callback
     * @param array $parameters
     * @return array
     *
     * @throws \ReflectionException
     */
    protected static function getMethodDependencies(DIContainerInterface $container, $callback, array $parameters = [])
    {
        $dependencies = [];
        /**
         * Сделано для передачи объектов с неправильным именем парамера
         * @warning Возможна Коллизия параметров: method(CLass1 $p1, Class1 $p2) !!!! один случай на миллион
         * @used-by addDependencyForCallParameter()
         */
        $classes = [];
        foreach ($parameters as $p) {
            if (\is_object($p)) {
                $classes[\get_class($p)] = $p;
            }
        }
        foreach (static::getCallReflector($callback)->getParameters() as $parameter) {
            static::addDependencyForCallParameter($container, $parameter, $parameters, $dependencies, $classes);
        }

        return array_merge(array_values($dependencies), array_values($parameters));
        //return $dependencies;
    }

    /**
     * Get the proper reflection instance for the given callback.
     *
     * @param array|string|\Closure $callback
     * @return \ReflectionFunctionAbstract
     *
     * @throws \ReflectionException
     */
    protected static function getCallReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        }

        return is_array($callback)
            ? new ReflectionMethod($callback[0], $callback[1])
            : new ReflectionFunction($callback);
    }

    /**
     * Get the dependency for the given call parameter.
     *
     * @param DIContainerInterface $container
     * @param \ReflectionParameter $parameter
     * @param array $parameters
     * @param array $dependencies
     * @param array $classes
     * @return void
     * @throws BindingResolutionException
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    protected static function addDependencyForCallParameter(DIContainerInterface $container, \ReflectionParameter $parameter, array &$parameters, array &$dependencies, array $classes = [])
    {
        if (array_key_exists($parameter->name, $parameters)) {
            $dependencies[] = $parameters[$parameter->name];

            unset($parameters[$parameter->name]);
        } elseif (($class = Reflection::getParameterClassName($parameter))) {
            if (array_key_exists($class, $parameters)) {
                $dependencies[] = $parameters[$class];
                unset($parameters[$class]);
            } elseif (isset($classes[$class])) {
                /**
                 * @warning Возможна Коллизия параметров: method(CLass1 $p1, Class1 $p2) !!!! один случай на миллион
                 */
                $dependencies[] = $classes[$class];
                unset($parameters[$class]);
            } else {
                // todo : Если стоит значение по умолчанию null создавать?
                $dependencies[] = $container->make($class);
            }


        } elseif ($parameter->isDefaultValueAvailable()) {
            $dependencies[] = $parameter->getDefaultValue();
        } else {
            throw new BindingResolutionException('Parameter [' . $parameter->getName() . '] is not find!');
        }
    }
}
