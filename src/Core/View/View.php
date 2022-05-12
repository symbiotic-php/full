<?php

namespace Symbiotic\Core\View;


use Symbiotic\Apps\Application;
use Symbiotic\Apps\ApplicationInterface;
use Symbiotic\Apps\AppsRepositoryInterface;
use Symbiotic\Core\CoreInterface;
use Symbiotic\Core\Support\RenderableInterface;
use Symbiotic\Core\Support\Str;
use Symbiotic\Packages\TemplatesRepositoryInterface;
use Symbiotic\Routing\RouteInterface;


/**
 * Без параметров возвращает текущий модуль {@see ApplicationInterface}
 *  или необходимый параметр из контейнера текущего приложения
 *
 * @param null $abstract
 * @param array $parameters
 * @return ApplicationInterface|Application| mixed
 *
 */
function app($abstract = null, array $parameters = [])
{
    $container = View::getCurrentContainer();
    if (is_null($abstract)) {
        return $container;
    }

    return $container->make($abstract, $parameters);
}

/**
 * @param string $str
 * @return string
 * @throws \Exception
 */
function appendApp(string $str): string
{
    /* @throws \Exception Если нет текущего пакета в view */
    return !is_array(Str::sc($str)) ? View::getCurrentPackageId() . '::' . $str : $str;
}

/**
 * @param string $path
 * @param bool $absolute
 * @return string Uri файла приложения
 */
function asset($path = '', $absolute = true)
{
    return \_S\core('url')->asset(appendApp($path), $absolute);
}


/**
 * @param $name
 * @param array $parameters
 * @param bool $absolute
 * @return mixed
 * @throws \Exception
 */
function route($name, $parameters = [], $absolute = true)
{
    return \_S\core('url')->route(appendApp($name), $parameters, $absolute);
}

function settlementRoute($settlement, $name, $parameters = [], $absolute = true)
{
    return \_S\core('url')->route($settlement . ':' . appendApp($name), $parameters, $absolute);
}

function adminRoute($name, $parameters = [], $absolute = true)
{
    return settlementRoute('backend', $name, $parameters, $absolute);
}

function apiRoute($name, $parameters = [], $absolute = true)
{
    return settlementRoute('api', $name, $parameters, $absolute);
}


/**
 * @param string $path
 * @param bool $absolute
 * @return string html style
 */
function css($path = '', $absolute = true)
{
    return '<link rel="stylesheet" href="' . asset($path, $absolute) . '">';
}

function js($path = '', $absolute = true, $type = 'text/javascript')
{
    return '<script type="' . $type . '" src="' . asset($path, $absolute) . '"></script>';
}

/**
 * @param string|array $handler \My\CLass@methodName or ['\My\Class', 'methodName']
 * @param null $app_id
 * @return mixed
 */
function action(string|array $handler, $app_id = null)
{
    return ($app_id ? \_S\core(AppsRepositoryInterface::class)->getBootedApp($app_id) : app())->call($handler);
}

/**
 * @param $template
 * @param array|null $vars
 * @return View
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */
function render($template, array $vars = null)
{
    return View::make($template)->with(is_array($vars) ? $vars : []);
}

/**
 * @param string $message
 * @param array|null $vars
 * @param null $lang
 * @return string
 * @throws \Exception
 * @uses \_S\lang()
 */
function lang(string $message, array $vars = null, $lang = null): string
{
    return \_S\lang(appendApp($message), $vars, $lang);
}


/**
 * Class View
 *
 * @package Symbiotic\View
 *
 */
class View implements RenderableInterface
{

    /**
     * @var CoreInterface | array $core = [
     *       'config' => new \Symbiotic\Config(),
     *       'router' => new \Symbiotic\Routing\Router(),
     *       'apps' => new \Symbiotic\Contracts\Apps\AppsRepository()
     *
     * ]
     * @used-by View::setContainer()
     * @see     CoreProvider::boot()
     */
    protected static $core;
    /**
     * @var array
     * @used-by setContainer() -  в методе очищается переменная и устанавливается последний
     * @used-by View::render() -  в методе устанавливается последним текущий модуль и потом удаляется
     */
    protected static $current_container = [];
    /**
     * All of the captured sections.
     *
     * @var array
     */
    public $sections = [];
    /**
     * The last section on which injection was started.
     *
     * @var array
     */
    public $last = [];
    protected $template = '';
    protected $vars = [];
    /**
     * @see ApplicationInterface::getId()
     * @var null|string
     */
    protected $app_id;

    /**
     * View constructor.
     * @param string $path
     * @param array $vars
     * @param null $app_id
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(string $path, array $vars = [], $app_id = null)
    {
        $this->vars = $vars;
        // Template find
        $id = null;
        if (is_string($app_id)) {
            $id = $app_id;
        } else if (is_array(($sc = Str::sc($path)))) {
            $id = $sc[0];
            $path = $sc[1];
        } else {
            /**
             * @var RouteInterface | null $route
             */
            $route = static::$core->get('route');
            if ($route && $route->getApp() !== null) {
                $id = $route->getApp();
            }
        }

        $this->app_id = $id;

        $this->template = static::$core->get(TemplatesRepositoryInterface::class)->getTemplate($id, $path);

    }

    /**
     * @param $template
     * @param array $vars
     * @param null $app_id
     * @return static
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function make($template, array $vars = [], $app_id = null)
    {
        return new static($template, $vars, $app_id);
    }

    /**
     * @param CoreInterface $app
     * @uses    View::$core
     * @used-by CoreProvider::boot()
     */
    public static function setContainer(CoreInterface $app)
    {
        static::$core = $app;
        static::$current_container = [$app];
    }

    public static function getCurrentPackageId()
    {
        $app = end(static::$current_container);
        if (is_string($app)) {
            return $app;
        }
        if ($app instanceof ApplicationInterface) {
            return $app->getId();
        }
        throw new \Exception('Container is not app!');
    }

    public static function getCurrentContainer()
    {
        $app = end(static::$current_container);
        // Загружаем контейнер приложения только по запросу (приложения поставляющие только шаблоны не имеют контейнера)
        if (is_string($app)) {
            if ($app === 'app' && static::$core->has('app')) {
                $app = static::$core['app'];
                $app->bootstrap();

            } elseif ($apps = static::$core['apps']) {
                /**
                 * @var AppsRepositoryInterface $apps
                 */
                if (!$apps->has($app)) {
                    throw new \Exception('Not exists App [' . $app . ']');
                }
                $app = $apps->getBootedApp($app);
            } else {
                throw new \Exception('Not exists App [' . $app . ']');
            }
            array_pop(static::$current_container);
            static::$current_container[] = $app;
        }


        return $app;
    }

    public function url($path = '', $absolute = true)
    {//todo: yf aeyrwbb gthtdtcnb
        return static::$core['url']->to($this->prepareModulePath($path), $absolute);
    }

    protected function prepareModulePath($path)
    {
        if (!is_array(Str::sc($path))) {
            $path = $this->app_id . '::' . $path;
        }

        return $path;
    }

    public function asset($path = '', $absolute = true)
    {
        return static::$core['url']->asset($this->prepareModulePath($path), $absolute);
    }

    public function route($path = '', $absolute = true)
    {
        return static::$core['url']->asset($this->prepareModulePath($path), $absolute);
    }

    /**
     * Stop injecting content into a section and return its contents.
     *
     * @return string
     */
    public function yield_section()
    {
        return $this->fetch($this->stop());
    }

    /**
     * @param null $content
     * @return false|string
     * @throws \Exception
     */
    public function fetch($content = null)
    {
        if(null === $content) {
            $content = $this;
        }
        ob_start();
        if (is_callable($content)) {
            $content();
        } elseif ($content instanceof RenderableInterface) {
            $content->render();
        } else {
            echo $content;
        }
        return ob_get_clean();
    }

    /**
     * Stop injecting content into a section.
     *
     * @return string
     */
    public function stop()
    {
        $this->extend($last = array_pop($this->last), ob_get_clean());

        return $last;
    }

    /**
     * Extend the content in a given section.
     *
     * @param string $section
     * @param string $content
     * @return void
     */
    protected function extend($section, $content)
    {
        if (isset($this->sections[$section])) {
            $this->sections[$section] =
                ($content instanceof View) ?
                    function () use ($content) {
                        $content->render();// че это такое??????
                    }
                    : str_replace('@parent', $content, $this->sections[$section]);
        } else {
            $this->sections[$section] = $content;
        }
    }

    public function render()
    {
        static::$current_container[] = $this->app_id;
        if (!empty($this->template)) {
            extract($this->vars);
            $__view = $this;
            try {
                eval($this->getTemplate());
            } catch (\ParseError $e) {
                throw new \Exception($e->getMessage() . PHP_EOL . $this->template, $e->getCode(), $e);
            }


        }
        array_pop(static::$current_container);
    }

    protected function getTemplate()
    {
        return 'use function ' . __NAMESPACE__ . '\\{app,asset,route,css,js,adminRoute,apiRoute,action,render,lang};' . PHP_EOL . ' ?>' . $this->template;
    }

    /**
     * Append content to a given section.
     *
     * @param string $section
     * @param string $content
     * @return void
     */
    public function append($section, $content)
    {
        if (isset($this->sections[$section])) {
            $this->sections[$section] .= $content;
        } else {
            $this->sections[$section] = $content;
        }
    }

    /**
     * Get the string contents of a section.
     *
     * @param string $section
     * @return string
     */
    public function yield($section)
    {
        if (isset($this->sections[$section])) {
            $section = $this->sections[$section];
            if (is_callable($section)) {
                $section();
            } elseif ($section instanceof View) {
                $section->setSections($this->sections);
                $section->render();
            } elseif ($section instanceof RenderableInterface) {
                $section->render();
            } else {
                echo (string)$section;
            }
        }
    }

    public function setSections($sections)
    {
        $this->sections = $sections;
    }

    public function hasSection(string $name)
    {
        return isset($this->sections[$name]);
    }

    /**
     * Специальный метод для передачи шаблона в слой
     *
     * @param string $template
     * @param $content_template
     * @param array $vars
     * @return static
     */
    public function layout(string $template, $content_template = null, $vars = [], $before = false)
    {
        $app_id = $this->app_id;
        if (is_array(($sc = Str::sc($template)))) {
            $app_id = $sc[0];
            $template = $sc[1];
        }
        if (!is_null($content_template)) {
            $this->template = $content_template;
        }

        $view = new static($template, $vars, $app_id);
        if ($before) {
            $content = $this->fetch($this);
            $sections = $this->sections;
            $sections['content'] = $content;
            $view->setSections($sections);
        } else {
            $view->inject('content', $this);
        }

        return $view;
    }

    /**
     * Inject inline content into a section.
     *
     * This is helpful for injecting simple strings such as page titles.
     *
     * <code>
     *        // Inject inline content into the "header" section
     *        Section::inject('header', '<title>Symbiotic</title>');
     * </code>
     *
     * @param string $section
     * @param string $content
     * @return void
     */
    public function inject($section, $content)
    {
        $this->start($section, $content);
    }

    /**
     * Start injecting content into a section.
     *
     * <code>
     *        // Start injecting into the "header" section
     *        Section::start('header');
     *
     *        // Inject a raw string into the "header" section without buffering
     *        Section::start('header', '<title>Symbiotic</title>');
     * </code>
     *
     * @param string $section
     * @param string|\Closure $content
     * @return void
     */
    public function start($section, $content = null)
    {
        if ($content === null) {
            ob_start() and $this->last[] = $section;
        } else {
            $this->extend($section, $content);
        }
    }

    public function with(array $vars)
    {
        $this->vars = $vars;

        return $this;
    }

    public function __toString()
    {
        return $this->fetch($this);
    }
}