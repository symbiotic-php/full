<?php

namespace Dissonance\Apps;

use Dissonance\Container\DIContainerInterface;
use Dissonance\Container\ServiceContainerInterface;


/**
 * Interface ApplicationInterface
 * @package dissonance/apps-сontracts
 *
 * @property \Dissonance\Core\Core|ApplicationInterface $app для плагинов контейнером будет роджительское приложение
 *
 */
interface  ApplicationInterface extends AppConfigInterface, DIContainerInterface, ServiceContainerInterface
{

    public function getBasePath(string $path = null);

    /**
     * @return string|null
     * @deprecated
     */
    public function getAssetsPath();

    /**
     * @return string|null
     * @deprecated
     */
    public function getResourcesPath();

    /**
     * @param array|\Closure[]|null $bootstraps
     */
    public function bootstrap(array $bootstraps = null): void;

} 