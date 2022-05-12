<?php

namespace Symbiotic\Core;


use Symbiotic\Container\{ArrayAccessTrait, Container, DIContainerInterface, ServiceContainerTrait, SingletonTrait};
use Symbiotic\Core\Bootstrap\{BootBootstrap, CoreBootstrap, ProvidersBootstrap};

/**
 * Class Core
 * @package Symbiotic/Core
 */
class Core extends Container implements CoreInterface
{

    use ServiceContainerTrait,
        ArrayAccessTrait,
        SingletonTrait;

    /**
     * Class names Runners {@see \Symbiotic\Core\Runner}
     * @var string[]
     */
    protected array $runners = [];

    /**
     * @var string|null
     */
    protected ?string $base_path;

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected array $bootstraps = [];

    /**
     * The bootstrap classes for the application.
     *
     * @var array
     */
    protected array $last_bootstraps = [
        ProvidersBootstrap::class,
        BootBootstrap::class,
    ];


    /**
     * используется для загрузки других скриптов после неуспешной отработки фреймворка
     * @var \Closure[]|array
     * @used-by Core::runNext()
     */
    protected array $then = [];

    /**
     * используется после успешной отработки фреймворка
     * @var \Closure[]|array
     * @used-by Core::runComplete()
     * @see     Core::onComplete()
     */
    protected array $complete = [];

    /**
     * @var \CLosure[]|array
     */
    protected array $before_handle = [];

    public function __construct(array $config = [])
    {
        $this->dependencyInjectionContainer = static::$instance = $this;
        $this->instance(DIContainerInterface::class, $this);
        $this->instance(CoreInterface::class, $this);

        $this->instance('bootstrap_config', $config);
        $this->base_path = rtrim(isset($config['base_path']) ? $config['base_path'] : __DIR__, '\\/');
        $this->runBootstrap(CoreBootstrap::class);
    }

    public function runBootstrap(string $class): void
    {
        if (class_exists($class)) {
            (new $class())->bootstrap($this);
        }
    }

    /**
     * @param string| string[] $bootstraps
     */
    public function addBootstraps(string|array $bootstraps): void
    {
        foreach ((array)$bootstraps as $v) {
            $this->bootstraps[] = $v;
        }
    }

    public function bootstrap(): void
    {
        if (!$this->isBooted()) {
            foreach (($this->bootstraps + $this->last_bootstraps) as $class) {
                $this->runBootstrap($class);
            }
        }

        $this->booted = true;
    }

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    public function addRunner(RunnerInterface $runner, $priority = null, string $name = null): void
    {
        /**
         * Для подмены
         */
        if (is_null($name)) {
            $name = \get_class($runner);
        }
        /// todo: надо сделать добавление с приоритетом
        /**
         * [id => [object,priority],,,,]
         */
        $this->runners[$name] = $runner;
    }

    public function run(): void
    {

        foreach ($this->runners as $runner) {
            /**
             * @var RunnerInterface $runner
             */
            $runner = new $runner($this);
            if ($runner->isHandle()) {
                $result = $runner->run();
                if ($result) {
                    $this->runComplete();
                    exit;
                } else {
                    $this->runNext();
                }
                break;
            }
        }
    }

    /**
     * событие завершения работы
     */
    public function runComplete(): void
    {
        foreach ($this->complete as $v) {
            $this->call($v);
        }
    }

    /**
     * Запускает отработку скриптов после фреймворка
     * @used-by run()
     */
    public function runNext(): void
    {
        foreach ($this->then as $v) {
            if ($this->call($v)) {
                return;
            }
        }
    }

    /**
     * @param \Closure $loader
     */
    public function beforeHandle(\Closure $loader): void
    {
        $this->before_handle[] = $loader;
    }

    /**
     * событие перед обработкой
     *
     * Можно использовать для подключения файлов необходимых для отработки контроллера или команды
     * @used-by  \Symbiotic\Http\Kernel\HttpRunner::run()
     */
    public function runBefore(): void
    {
        foreach ($this->before_handle as $v) {
            $this->call($v);
        }
    }

    public function onComplete(\Closure $complete): void
    {
        $this->complete[] = $complete;
    }

    /**
     *  Используется для загрузки других скриптов после неуспешной отработки фреймворка
     *  Замыкание должно вернуть true, чтобы прервать цепочку после себя
     *
     * @param \Closure $then
     */
    public function then(\Closure $then): void
    {
        $this->then[] = $then;
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @param string $path Optionally, a path to append to the base path
     * @return string
     *
     * @todo: Метод используется один раз, нужен ли он?
     */
    public function getBasePath($path = ''):string
    {
        return $this->base_path . ($path ? \_S\DS . $path : $path);
    }

}


