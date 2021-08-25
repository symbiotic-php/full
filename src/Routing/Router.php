<?php

namespace Dissonance\Routing;


/**
 * Class Router
 * @package Dissonance\Routing
 *
 */
class Router implements RouterInterface
{
    use AddRouteTrait;

    public static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    /**
     * @used-by group()
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * @used-by addRoute()
     *
     * @var array = [
     *     'GET' => [
     *        'pattern/test' => Route(),
     *        'pattern/test1' => Route(),
     *        // ....
     *      ],
     *      'POST' => [],
     *       // ....
     * ]
     */
    protected $routes = [];

    /**
     * @see addRoute()
     * @used-by getRoute()
     *
     * @var array
     */
    protected $named_routes = [];

    protected $domain = '';


    /**
     * Router constructor.
     */
    public function __construct()
    {
        foreach (static::$verbs as $verb) {
            $this->routes[$verb] = [];
        }
    }

    public function setRoutesDomain(string $domain)
    {
        $this->domain = $domain;
    }


    /**
     * Add route
     *
     * @param array |string $httpMethods
     * @param string $uri Uri pattern
     * @param array|string|\Closure $action = [
     *
     *                    'uses' => '\\Module\\Http\\EntityController@edit',//  \Closure | string
     *                     // optional params
     *                     'as' => 'module.entity.edit',
     *                     'module' => 'module_name',
     *                     'middleware' => ['\\Dissonance\\Http\\Middlewares\Auth', '\\Module\\Http\\Middlewares\Test']
     * ]
     *
     * @return Route
     */
    public function addRoute($httpMethods, string $uri, $action): RouteInterface
    {
        $httpMethods = array_map('strtoupper', (array)$httpMethods);
        $route = $this->createRoute($uri, $action, $httpMethods);
        $this->setRoute($route);

        return $route;
    }

    public function setRoute(RouteInterface $route)
    {
        if($this->domain && !$route->getDomain()) {
            $route->setDomain($this->domain);
        }
        foreach ($route->getAction()['methods'] as $method) {
            $this->routes[$method][$route->getPath()] = $route;
        }
        $name = $route->getName();
        if ($name) {
            $this->named_routes[$name] = $route;
        }

        return $route;
    }

    public $count_routes = 0;
    /**
     * @param string $uri
     * @param array|string|\Closure $action
     * @param array $httpMethods
     * @return Route
     */
    protected function createRoute(string $uri, $action, array $httpMethods)
    {
        $this->count_routes++;
        if (is_string($action) || $action instanceof \Closure) {
            $action = ['uses' => $action];
        }
        if (is_array($action)) {
            if (!empty($this->groupStack)) {
                $group = end($this->groupStack);
                // Merge group namespace with controller name
                if ((isset($action['uses']) && is_string($action['uses']))) {
                    $class = $action['uses'];
                    $action['uses'] = isset($group['namespace']) && strpos($class, '\\') !== 0
                        ? rtrim($group['namespace'], '\\') . '\\' . $class : $class;
                }

                // Merge other params (as, prefix, namespace,module)
                $action = static::mergeAttributes($action, $group);

                // Merge Uri with prefix
                $uri = trim(trim(isset($group['prefix']) ? $group['prefix'] : '', '/') . '/' . trim($uri, '/'), '/') ?: '/';
            }
        }
        $action['methods'] = $httpMethods;
        return new Route($uri, $action);
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getRoute(string $name): ? RouteInterface
    {
        return $this->named_routes[$name] ?? null;
    }


    /**
     * Create a route group with shared attributes.
     *
     * @param array $attributes
     * @param \Closure|callable| object $routes if object need __invoke method
     *
     * @return void
     */
    public function group(array $attributes, callable $routes)
    {
        $attributes = static::mergeAttributes($attributes, !empty($this->groupStack) ? end($this->groupStack) : []);

        $this->groupStack[] = $attributes;
        $routes($this);
        array_pop($this->groupStack);
    }


    /**
     * @param  $httpMethod
     * @param  $uri
     * @return Route|null
     */
    public function match(string $httpMethod, string $uri): ? RouteInterface
    {

        $uri = trim($uri, '/');
        $httpMethod = strtoupper($httpMethod);
        $all_routes = $this->getRoutes();
        $routes = isset($all_routes[$httpMethod]) ? $all_routes[$httpMethod] : [];

        /**
         * @var Route $route
         */

        foreach ($routes as $route) {
            $vars = [];
            $pattern = \preg_replace('/(^|[^\.])\*/ui', '$1.*?', \str_replace(array(' ', '.', '('), array('\s', '\.', '(?:'), $route->getPath()));
            if (\preg_match_all('/\{([a-z_]+):?([^\}]*)?\}/ui', $pattern, $match, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
                $offset = 0;
                foreach ($match as $m) {
                    $vars[] = $m[1][0];
                    $p = ($m[2][0]) ? $m[2][0] : '.*?';
                    $pattern = substr($pattern, 0, $offset + $m[0][1]) . '(' . $p . ')' . substr($pattern, $offset + $m[0][1] + strlen($m[0][0]));
                    $offset = $offset + strlen($p) + 2 - strlen($m[0][0]);
                }
            }
            if (preg_match('!^' . $pattern . '$!ui', $uri, $match)) {
                if ($vars) {
                    $route = clone $route;
                    array_shift($match);
                    foreach ($vars as $i => $v) {
                        if (isset($match[$i])) {
                            $route->setParam($v, $match[$i]);
                        }
                    }
                }

                return $route;
            }
        }
        return null;
    }

    protected static function mergeAttributes(array $new, array $old)
    {
        $as = 'as';
        if (isset($old[$as])) {
            $is_app = substr($old[$as], -2)==='::';
            $new[$as] = $old[$as] . (isset($new[$as]) ? ($is_app ? '' : '.') . $new[$as] : '');
        }
        $module = 'module';
        if (!isset($new[$module]) && isset($old[$module])) {
            $new[$module] = $old[$module];
        }
        $secure = 'secure';
        if (!isset($new[$secure]) && isset($old[$secure])) {
            $new[$secure] = $old[$secure];
        }
        $namespace = 'namespace';
        if (isset($new[$namespace])) {
            $new[$namespace] = isset($old[$namespace]) && strpos($new[$namespace], '\\') !== 0
                ? rtrim($old[$namespace], '\\') . '\\' . trim($new[$namespace], '\\')
                : '\\' . trim($new[$namespace], '\\');
        } elseif (isset($old[$namespace])) {
            $new[$namespace] = $old[$namespace];
        } else {
            $new[$namespace] = null;
        }

        $prefix = 'prefix';
        $old_p = isset($old[$prefix]) ? $old[$prefix] : null;
        $new[$prefix] = isset($new[$prefix]) ? trim($old_p, '/') . '/' . trim($new[$prefix], '/') : $old_p;

        foreach ([$as, $module, $namespace, $prefix] as $v) {
            if (array_key_exists($v, $old)) {
                unset($old[$v]);
            }
        }
        return array_merge_recursive($old, $new);
    }

    /**
     * @param null|string $httpMethod
     * @uses $routes - see structure
     *
     * @return array
     */
    public function getRoutes(string $httpMethod = null): array
    {
        if($httpMethod && in_array(strtoupper($httpMethod), static::$verbs)) {
            return $this->routes[strtoupper($httpMethod)];
        }
        return $this->routes;
    }

    /**
     * @param string $settlement
     * @return array|RouteInterface[]
     */
    public function getBySettlement(string $settlement):array
    {
        $routes = [];
        foreach ($this->named_routes as $v)
        {
            if (preg_match('/^'.preg_quote($settlement,'/').'/', $v->getName())) {
                $routes[$v->getName()] = $v;
            }
        }

        return $routes;
    }

}
