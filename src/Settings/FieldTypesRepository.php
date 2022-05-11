<?php

namespace Symbiotic\Settings;


/**
 * Class FieldTypesRepository
 * @package Symbiotic\Settings
 *
 * To add fields, subscribe to an event in the event('FieldTypesRepository::class','\My\HandlerObject') kernel
 */
class FieldTypesRepository
{
    protected array $types = [];

    /**
     * @param string $type the field type must include the application prefix: filesystems::path
     * @param \Closure $callback Should return the html code with the field
     * @see render()
     * @example function(array $field, $value = null):string {return '<code>';}
     */
    public function add(string $type, \Closure $callback)
    {
        $this->types[$type] = $callback;
    }

    /**
     * @param array $field
     * @param null|string|int|bool|mixed $value If null , then the value has not been set , this is to use the default value .
     * @return string
     */
    public function render(array $field, $value = null)
    {
        $type = $field['type'];
        if ($this->has($field['type'])) {
            $callback = $this->types[$field['type']];
            return $callback($field, $value);
        }
        throw  new \Exception('Field type [' . $type . '] not found!');
    }

    public function has(string $type)
    {
        return isset($this->types[$type]);
    }

}