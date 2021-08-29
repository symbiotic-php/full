<?php

namespace Symbiotic\Core;


interface RunnerInterface
{
    public function isHandle(): bool;

    /**
     * Возвращает результат отработки
     * при успешной отработке будет завершена работа
     * при неуспешной будет запущен обработчик {@see CoreInterface::runNext()}  продолжится работа скрипта
     * @return bool
     */
    public function run(): bool;
}
