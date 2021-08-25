<?php

namespace Dissonance\Core;

use Dissonance\Container\{DIContainerInterface, ServiceContainerInterface};

/**
 * Interface CoreInterface
 * @package Dissonance\Contracts
 */
interface CoreInterface extends DIContainerInterface, ServiceContainerInterface
{
    /**
     * @param string | string[] $bootstraps class name implemented {@see BootstrapInterface}
     * @return mixed
     */
    public function addBootstraps(string|array $bootstraps);

    /**
     * Determine if the application has booted.
     *
     * @return bool
     */
    public function isBooted(): bool;

    /**
     * Запускает инициализацию приложения
     *
     * @used-by  HttpKernelInterface::bootstrap()
     * @used-by  \Dissonance\Http\Kernel\HttpKernel::bootstrap()
     */
    public function bootstrap(): void;

    public function runBootstrap($class): void;

    public function addRunner(RunnerInterface $runner): void;

    public function run(): void;
}
