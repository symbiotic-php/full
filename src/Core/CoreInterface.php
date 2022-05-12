<?php

namespace Symbiotic\Core;

use Symbiotic\Container\{DIContainerInterface, ServiceContainerInterface};

/**
 * Interface CoreInterface
 * @package Symbiotic\Contracts
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
     * @used-by  \Symbiotic\Http\Kernel\HttpKernel::bootstrap()
     */
    public function bootstrap(): void;

    /**
     * @param string $class
     */
    public function runBootstrap(string $class): void;

    public function addRunner(RunnerInterface $runner, $priority = null, string $name = null): void;

    public function run(): void;

    /**
     * Если будет определено приложение для обработки запроса
     * то выполнится функция в которой можно подключить файлы
     *
     * @param \Closure $loader
     */
    public function beforeHandle(\Closure $loader):void;

    /**
     * Запуск лоадера перед обработкой
     *
     * @used-by CoreInterface::run()
     */
    public function runBefore():void;

    /**
     * Запуск события после успешной отработки фреймворка
     *
     * @used-by CoreInterface::run()
     */
    public function runComplete():void;


    /**
     * @used-by CoreInterface::runComplete()
     * @param \Closure $complete
     */
    public function onComplete(\Closure $complete):void;

    /**
     *  Используется для загрузки других скриптов после неуспешной отработки фреймворка
     *
     * @param \Closure $loader
     */
    public function then(\Closure $loader):void;

    /**
     * Запускает отработку скриптов после фреймворка
     *
     * @used-by Core::run()
     */
    public function runNext():void;

    /**
     * Get the base path of the Laravel installation.
     *
     * @param string $path Optionally, a path to append to the base path
     * @return string
     *
     * @todo: Метод используется один раз, нужен ли он?
     */
    public function getBasePath($path = ''):string;
}
