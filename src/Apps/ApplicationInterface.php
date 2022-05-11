<?php

namespace Symbiotic\Apps;

use Symbiotic\Container\DIContainerInterface;
use Symbiotic\Container\ServiceContainerInterface;


/**
 * Interface ApplicationInterface
 * @package symbiotic/apps-сontracts
 *
 * @property \Symbiotic\Core\Core|ApplicationInterface $app для плагинов контейнером будет родительское приложение
 *
 */
interface ApplicationInterface extends AppConfigInterface, DIContainerInterface, ServiceContainerInterface
{

    /**
     * Путь от Корневой папка пакета
     * @param string|null $path
     * @return string|null
     */
    public function getBasePath(string $path = null): ?string;

    /**
     * Возвращает адрес файла
     * @param string $uri_path
     * @return mixed
     */
    public function asset(string $uri_path);

    /**
     * @return string|null
     * @deprecated
     */
    public function getAssetsPath(): ?string;

    /**
     * @return string|null
     * @deprecated
     */
    public function getResourcesPath(): ?string;

    /**
     * @param array|\Closure[]|null $bootstraps
     */
    public function bootstrap(array $bootstraps = null): void;

} 