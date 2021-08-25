<?php

namespace Dissonance\Core;


use Dissonance\Container\{DIContainerInterface, ArrayAccessTrait, SingletonTrait, Container, ServiceContainerTrait};
use Dissonance\Core\Bootstrap\{BootBootstrap, CoreBootstrap, ProvidersBootstrap};


/**
 * Class Core
 * @package Dissonance/Core
 */
class Core extends Container implements CoreInterface
{

    use ServiceContainerTrait,
        ArrayAccessTrait,
        SingletonTrait;

    /**
     * Class names Runners {@see \Dissonance\Core\Runner}
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
     * Массив ключей разрешенных сервисов для кеширования
     * @var array|string[]
     */
    protected array $allow_cached = [];

    public function __construct(array $config = [])
    {
        $this->dependencyInjectionContainer = static::$instance = $this;
        $this->instance(DIContainerInterface::class, $this);
        $this->instance(CoreInterface::class, $this);

        $this->instance('bootstrap_config', $config);
        $this->base_path = rtrim(isset($config['base_path']) ? $config['base_path'] : __DIR__, '\\/');
        $this->runBootstrap(CoreBootstrap::class);
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

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
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

    public function runBootstrap($class): void
    {
        if (class_exists($class)) {
            (new $class())->bootstrap($this);
        }
    }

    public function addRunner(RunnerInterface $runner): void
    {
        $this->runners[] = $runner;
    }

    public function run(): void
    {

        foreach ($this->runners as $runner) {
            /**
             * @var RunnerInterface $runner
             */
            $runner = new $runner($this);
            if ($runner->isHandle()) {
                $runner->run();
                break;
            }
        }
    }


    /**
     * Get the base path of the Laravel installation.
     *
     * @param string $path Optionally, a path to append to the base path
     * @return string
     *
     * @todo: Метод используется один раз, нужен ли он?
     */
    public function getBasePath($path = '')
    {
        return $this->base_path . ($path ? \_DS\DS . $path : $path);
    }

}


