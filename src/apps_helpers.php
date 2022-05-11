<?php

namespace _S;

use Symbiotic\Apps\ApplicationInterface;
use Symbiotic\Apps\AppsRepositoryInterface;

/**
 * Возвращает контейнер приложения без инициализации
 * для инициализации используйте метод  {@uses \Symbiotic\Apps\ApplicationInterface::bootstrap()}
 *
 * @param string $id
 * @return ApplicationInterface|null
 * @throws \Psr\Container\ContainerExceptionInterface Если нет сервиса приложений
 */
function app(string $id): ?ApplicationInterface
{
    $apps = \_S\core(AppsRepositoryInterface::class);
    return $apps->has($id) ? $apps->get($id) : null;
}
