<?php

namespace _S;

use Symbiotic\Core\Config;
use Symbiotic\Core\Core;
use Symbiotic\Core\Support\Arr;
use Symbiotic\Core\Support\Collection;
use Symbiotic\Core\Support\Str;
use Symbiotic\Core\View\View;


const DS = DIRECTORY_SEPARATOR;

function core($abstract = null, array $parameters = null)
{
     $core = Core::getInstance();
    if (is_null($abstract)) {
        return $core;
    }
    return is_null($parameters) ? $core->get($abstract) : $core->make($abstract, $parameters);
}


if (!function_exists('_S\\config')) {
    /**
     * Get Config data
     * @param string|null $key
     * @param null $default
     *
     * @return Config|null|mixed
     */
    function config(string $key = null, $default = null)
    {
        $config = core('config');
        return is_null($key) ? $config : ($config->has($key) ? $config->get($key) : $default);
    }
}

if (!function_exists('_S\\event')) {
    /**
     * Run event
     *
     * @param object $event
     *
     * @return object $event
     */
    function event(object $event)
    {
        /**
         * @uses \Symbiotic\Event\EventDispatcher::dispatch()
         */
        return core('events')->dispatch($event);
    }
}

if (!function_exists('_S\\listen')) {
    /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
     * @param string $event the class name or an arbitrary event name
     * (with an arbitrary name, you need a custom dispatcher not for PSR)
     *
     * @param \Closure|string $handler function or class name of the handler
     * The event handler class must implement the handle method  (...$params) or __invoke(...$params)
     * <Important:> When adding listeners as class names, you will need to adapt them to \Closure when you return them in the getListenersForEvent() method!!!
     *
     * @return void
     * @throws \Exception if lisreners Service not found
     */
    function listen(string $event, $handler): void
    {
        core('listeners')->add($event, $handler);
    }
}

if (!function_exists('_S\\route')) {

    /**
     * Generate the URL to a named route.
     *
     * @param array|string $name
     * @param mixed $parameters
     * @param bool $absolute
     * @return string
     * @uses \Symbiotic\Routing\UrlGeneratorInterface
     */
    function route($name, $parameters = [], $absolute = true)
    {
        return core('url')->route($name, $parameters, $absolute);
    }
}
/**
 * TODO add lang service
 * @used-by \Symbiotic\Core\View\lang()
 * @param string $message
 * @param array|null $vars
 * @param null $lang
 * @return string
 */
function lang(string $message, array $vars = null, $lang = null): string
{
    //todo: translate
    if(is_array($vars)) {
        $replaces = [];
        foreach ($vars as $k => $v) {
            $replaces[':'.(string)$k] = $v;
        }
        return str_replace(array_keys($replaces), $replaces, $message);
    }

    return $message;

}

if (!function_exists('_S\\view')) {

    /**
     * Make View
     * @param string $path
     * @param array $vars
     * @param null $app_id
     *
     * @return View
     * @throws
     */
    function view(string $path, array $vars = [], $app_id = null)
    {
        return View::make($path, $vars, $app_id);
    }
}

if (!function_exists('_S\\asset')) {
    /**
     * @param string $path
     * @param bool $absolute
     * @return string Uri файла приложения
     */
    function asset($path = '', $absolute = true)
    {
        return \_S\core('url')->asset($path, $absolute);
    }
}

if (!function_exists('_S\\camel_case')) {
    /**
     * Convert a value to camel case.
     *
     * @param string $value
     * @return string
     *
     * @deprecated Str::camel() should be used directly instead. Will be removed in Laravel 5.9.
     */
    function camel_case($value)
    {
        return Str::camel($value);
    }
}

if (!function_exists('_S\\class_basename')) {
    /**
     * Get the class "basename" of the given object / class.
     *
     * @param string|object $class
     * @return string
     */
    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('_S\\collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param mixed $value
     * @return Collection
     */
    function collect($value = null)
    {
        return new Collection($value);
    }
}

if (!function_exists('_S\\data_fill')) {
    /**
     * Fill in data where it's missing.
     *
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @return mixed
     */
    function data_fill(&$target, $key, $value)
    {
        return data_set($target, $key, $value, false);
    }
}

if (!function_exists('_S\\data_get')) {
    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param mixed $target
     * @param string|array|int $key
     * @param mixed $default
     * @return mixed
     */
    function data_get($target, $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (!is_null($segment = array_shift($key))) {
            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_array($target)) {
                    return value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (!function_exists('_S\\data_set')) {
    /**
     * Set an item on an array or object using dot notation.
     *
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @param bool $overwrite
     * @return mixed
     */
    function data_set(&$target, $key, $value, $overwrite = true)
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (!Arr::accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (Arr::accessible($target)) {
            if ($segments) {
                if (!Arr::exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !Arr::exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}


if (!function_exists('_S\\ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     * @uses \Symbiotic\Core\Support\Str::endsWith()
     * @deprecated \Symbiotic\Str::endsWith() should be used directly instead. Will be removed in Laravel 5.9.
     */
    function ends_with($haystack, $needles)
    {
        return Str::endsWith($haystack, $needles);
    }
}


if (!function_exists('_S\\blank')) {
    /**
     * Determine if the given value is "blank".
     *
     * @param mixed $value
     * @return bool
     */
    function blank($value)
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof \Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }
}

if (!function_exists('_S\\filled')) {
    /**
     * Determine if a value is "filled".
     *
     * @param mixed $value
     * @return bool
     */
    function filled($value)
    {
        return !blank($value);
    }
}
/*

if (!function_exists('_S\\preg_replace_array')) {
    /!!**
     * Replace a given pattern with each value in the array in sequentially.
     *
     * @param string $pattern
     * @param array $replacements
     * @param string $subject
     * @return string
     *!!/
    function preg_replace_array($pattern, array $replacements, $subject)
    {
        return preg_replace_callback($pattern, function () use (&$replacements) {
            foreach ($replacements as $key => $value) {
                return array_shift($replacements);
            }
        }, $subject);
    }
}*/


if (!function_exists('_S\\snake_case')) {
    /**
     * Convert a string to snake case.
     *
     * @param string $value
     * @param string $delimiter
     * @return string
     *
     * @deprecated Str::snake() should be used directly instead. Will be removed in Laravel 5.9.
     */
    function snake_case($value, $delimiter = '_')
    {
        return Str::snake($value, $delimiter);
    }
}




//if (!function_exists('_S\\throw_if')) {
//    /**
//     * Throw the given exception if the given condition is true.
//     *
//     * @param mixed $condition
//     * @param \Throwable|string $exception
//     * @param array ...$parameters
//     * @return mixed
//     *
//     * @throws \Throwable
//     */
//    function throw_if($condition, $exception, ...$parameters)
//    {
//        if ($condition) {
//            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
//        }
//
//        return $condition;
//    }
//}

//if (!function_exists('_S\\throw_unless')) {
//    /**
//     * Throw the given exception unless the given condition is true.
//     *
//     * @param mixed $condition
//     * @param \Throwable|string $exception
//     * @param array ...$parameters
//     * @return mixed
//     * @throws \Throwable
//     */
//    function throw_unless($condition, $exception, ...$parameters)
//    {
//        if (!$condition) {
//            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
//        }
//
//        return $condition;
//    }
//}

//if (!function_exists('_S\\title_case')) {
//    /**
//     * Convert a value to title case.
//     *
//     * @param string $value
//     * @return string
//     *
//     * @deprecated Str::title() should be used directly instead. Will be removed in Laravel 5.9.
//     */
//    function title_case($value)
//    {
//        return Str::title($value);
//    }
//}

if (!function_exists('_S\\transform')) {
    /**
     * Transform the given value if it is present.
     *
     * @param mixed $value
     * @param callable $callback
     * @param mixed $default
     * @return mixed|null
     */
    function transform($value, callable $callback, $default = null)
    {
        if (filled($value)) {
            return $callback($value);
        }

        if (is_callable($default)) {
            return $default($value);
        }

        return $default;
    }
}

if (!function_exists('_S\\value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value
     * @return mixed
     */
    function value($value)
    {
        return is_callable($value) ? $value() : $value;
    }
}


if (!function_exists('_S\\with')) {
    /**
     * Return the given value, optionally passed through the given callback.
     *
     * @param mixed $value
     * @param callable|null $callback
     * @return mixed
     */
    function with($value, callable $callback = null)
    {
        return is_null($callback) ? $value : $callback($value);
    }
}


