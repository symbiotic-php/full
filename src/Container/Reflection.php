<?php

namespace Symbiotic\Container;


class Reflection
{

    /**
     * @param \ReflectionParameter $parameter
     * @return string|null
     */
    public static function getParameterClassName(\ReflectionParameter $parameter): ?string
    {


        if (\PHP_VERSION_ID >= 70000) {
            $type = $parameter->getType();
            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                return null;
            }
            $name = $type->getName();
            if (!is_null($class = $parameter->getDeclaringClass())) {
                if ($name === 'self') {
                    return $class->getName();
                }

                if ($name === 'parent' && $parent = $class->getParentClass()) {
                    return $parent->getName();
                }
            }

            return $name;
        } else {
            return $parameter->getClass() ? $parameter->getClass()->getName() : null;
        }

    }
}
