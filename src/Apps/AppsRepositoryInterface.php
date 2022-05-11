<?php

namespace Symbiotic\Apps;

interface AppsRepositoryInterface
{
    /**
     * Нужно перенести в репозиторий пакетов наверно
     *
     * @return array
     */
    public function enabled(): array;

    /**
     * Получение массива всех приложений
     *
     * @return  array
     */
    public function all(): array;

    /**
     * Получение всех ID приложений
     *
     * @return string[]
     */
    public function getIds():array;

    /**
     * Возвращает объект конфигурации приложения
     *
     * @param string $id
     * @return AppConfigInterface|null
     */
    public function getConfig(string $id): ?AppConfigInterface;

    /**
     * Возвращает контейнер приложения
     *
     * @param string $id
     * @return ApplicationInterface
     * @throws \Exception Если не найдено приложение, проверяйте через {@see has()}
     */
    public function get(string $id): ApplicationInterface;

    /**
     * Проверка наличия приложения
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id);

    /**
     * Возвращает контейнер приложения с загруженными сервисами
     *
     * @param string $id
     * @return ApplicationInterface
     * @throws \Exception  Если не найдено приложение, проверяйте через {@see has()}
     */
    public function getBootedApp(string $id): ApplicationInterface;

    /**
     * Возвращает массив ID плагинов приложения
     *
     * @param string $app_id
     * @return array = ['app1','app2',....]
     */
    public function getPluginsIds(string $app_id);

    /**
     * Добавление конфигурации приложения
     *
     * (Можно использовать для создания плагинов для нескольких приложений)
     *
     * @param array $config
     * @return mixed
     */
    public function addApp(array $config);
}