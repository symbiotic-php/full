<?php

namespace Symbiotic\Container;


trait MethodBindingsTrait
{

    /**
     * The container's method bindings.
     *
     * @var \Closure[]
     */
    protected array $methodBindings = [];

    /**
     * Determine if the container has a method binding.
     *
     * @param string $method
     * @return bool
     */
    public function hasMethodBinding(string $method): bool
    {
        return isset($this->methodBindings[$method]);
    }

    /**
     * Bind a callback to resolve with Container::call.
     *
     * @param array|string $method
     * @param \Closure $callback
     * @return void
     */
    public function bindMethod(string|array $method, \Closure $callback)
    {
        $this->methodBindings[$this->parseBindMethod($method)] = $callback;
    }

    /**
     * Get the method to be bound in 'class@ method' format.
     *
     * @param array|string $method
     * @return string
     */
    protected function parseBindMethod(array|string $method)
    {
        if (is_array($method)) {
            return $method[0] . '@' . $method[1];
        }

        return $method;
    }

    /**
     * Get the method binding for the given method.
     *
     * @param string $method
     * @param mixed $instance
     * @return mixed
     */
    public function callMethodBinding(string $method, $instance)
    {
        return call_user_func($this->methodBindings[$method], $instance, $this);
    }

}