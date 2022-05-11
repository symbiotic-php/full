<?php

namespace Symbiotic\Routing;

use Symbiotic\Core\Support\{Arr, Str};


class UrlGenerator implements UrlGeneratorInterface
{

    /**
     * The named parameter defaults.
     *
     * @var array
     */
    public array $defaultParameters = [];

    /**
     * Characters that should not be URL encoded.
     *
     * @var array
     */
    public array $dontEncode = [
        '%2F' => '/',
        '%40' => '@',
        '%3A' => ':',
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
        '%3F' => '?',
        '%26' => '&',
        '%23' => '#',
        '%25' => '%',
    ];


    protected string $base_uri;

    protected string $assets_path;

    /**
     * @var RouterInterface
     */
    protected RouterInterface $router;

    /**
     * UrlGenerator constructor.
     * @param RouterInterface $router
     * @param string $base_uri {@see Provider::registerUriGenerator()}
     * @param string $assets_path {@see Provider::registerUriGenerator()}
     */
    public function __construct(RouterInterface $router, string $base_uri = '', string $assets_path = 'assets')
    {
        $this->router = $router;
        $this->base_uri = rtrim($base_uri, '/');
        $this->assets_path = $assets_path;

    }

    public function asset(string $path = '')
    {
        return $this->to($this->assets_path . '/' . $this->preparePath($path));
    }

    public function to(string $path = '')
    {
        return $this->base_uri . '/' . $this->preparePath($path);
    }

    /**
     * @param $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     * @throws \Exception
     */
    public function route(string $name, array $parameters = [], bool $absolute = true)
    {

        $route = $this->router->getRoute($name);
        if (!$route) {
            throw new \Exception('Not find route by name: ' . $name);
        }


        $uri = $this->addQueryString(
            $this->replaceRouteParameters($route->getPath(), $parameters),
            $parameters
        );

        if (preg_match('/\{.*?\}/', $uri)) {
            throw new \Exception('Required  param not replaced: ' . $uri);
        }

        $uri = strtr(rawurlencode($uri), $this->dontEncode);
        $uri = $this->base_uri . '/' . ltrim($uri, '/');
        if ($absolute) {
            $uri = 'http' . ($route->getSecure() ? 's' : '') . '://' . $route->getDomain() . $uri;
        }

        return $uri;
    }


    /**
     * @param string $path
     * @return string
     */
    protected function preparePath(string $path)
    {
        if (is_array(($sc = Str::sc($path)))) {
            $path = $sc[0] . '/' . $sc[1];
        }
        return ltrim($path, '/');
    }

    /**
     * Add a query string to the URI.
     *
     * @param string $uri
     * @param array $parameters
     * @return mixed|string
     */
    protected function addQueryString(string $uri, array $parameters)
    {
        // If the URI has a fragment we will move it to the end of this URI since it will
        // need to come after any query string that may be added to the URL else it is
        // not going to be available. We will remove it then append it back on here.
        if (!is_null($fragment = parse_url($uri, PHP_URL_FRAGMENT))) {
            $uri = preg_replace('/#.*/', '', $uri);
        }

        $uri .= $this->getRouteQueryString($parameters);

        return is_null($fragment) ? $uri : $uri . "#{$fragment}";
    }

    /**
     * Get the query string for a given route.
     *
     * @param array $parameters
     * @return string
     */
    protected function getRouteQueryString(array $parameters)
    {
        // First we will get all of the string parameters that are remaining after we
        // have replaced the route wildcards. We'll then build a query string from
        // these string parameters then use it as a starting point for the rest.
        if (count($parameters) === 0) {
            return '';
        }

        $query = http_build_query($keyed = $this->getStringParameters($parameters), null, '&', PHP_QUERY_RFC3986);

        // Lastly, if there are still parameters remaining, we will fetch the numeric
        // parameters that are in the array and add them to the query string or we
        // will make the initial query string if it wasn't started with strings.
        if (count($keyed) < count($parameters)) {
            $query .= '&' . implode(
                    '&', $this->getNumericParameters($parameters)
                );
        }

        return '?' . trim($query, '&');
    }

    /**
     * Get the string parameters from a given list.
     *
     * @param array $parameters
     * @return array
     */
    protected function getStringParameters(array $parameters)
    {
        return array_filter($parameters, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get the numeric parameters from a given list.
     *
     * @param array $parameters
     * @return array
     */
    protected function getNumericParameters(array $parameters)
    {
        return array_filter($parameters, 'is_numeric', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Replace all of the wildcard parameters for a route path.
     *
     * @param string $path
     * @param array $parameters
     * @return string
     */
    protected function replaceRouteParameters($path, array &$parameters)
    {
        $path = $this->replaceNamedParameters($path, $parameters);

        $path = preg_replace_callback('/\{.*?\}/', function ($match) use (&$parameters) {
            return (empty($parameters) && !Str::endsWith($match[0], '?}'))
                ? $match[0]
                : array_shift($parameters);
        }, $path);

        return preg_replace('/\{.*?\?\}/', '', $path);
    }

    /**
     * Replace all of the named parameters in the path.
     *
     * @param string $path
     * @param array $parameters
     * @return string
     */
    protected function replaceNamedParameters($path, &$parameters)
    {
        return preg_replace_callback('/\{(.*?)\??\}/', function ($m) use (&$parameters) {
            if (isset($parameters[$m[1]])) {
                return Arr::pull($parameters, $m[1]);
            } elseif (isset($this->defaultParameters[$m[1]])) {
                return $this->defaultParameters[$m[1]];
            }

            return $m[0];
        }, $path);
    }


}