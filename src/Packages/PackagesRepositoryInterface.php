<?php

namespace Symbiotic\Packages;


interface PackagesRepositoryInterface
{

    /**
     * Возвращает массив всех зарегистрированных пакетов
     *
     * @return array = ['id1','id_2', ....]
     */
    public function getIds(): array;

   /* /!**
     * @return array
     *!/
    public function enabled(): array;*/


    /**
     * Проверка наличия пакета
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Возвращает массив конфигурации пакета
     *
     * @param string $id
     * @return array
     *
     * @throws \Exception если не найден, перед вызовом проверяйте через has()
     */
    public function get(string $id): array;


    /**
     * Возвращает объект конфигурации пакета
     *
     * @param string $id
     * @return PackageConfig|null
     */
    public function getPackageConfig(string $id): ?PackageConfig;

    /**
     * Метод возвращает массив конфигурации всех пакетов
     *
     * Если вам нужен массив ID приложений используйте {@see getIds()}
     * Если вам нужно проверить существование пакета используйте  {@see has()}
     *
     * не надо делать так:
     *     $packages = $obj->all();
     *     if(isset($packages[$id])){}
     *
     * @return array
     */
    public function all(): array;

    /**
     * Добавление загрузчика пакетов
     *
     * @param PackagesLoaderInterface $loader
     */
    public function addPackagesLoader(PackagesLoaderInterface $loader): void;

    /**
     * Добавление конфигурации пакета
     *
     * @param array $config
     *
     * @used-by PackagesLoaderInterface::load()
     * @see     PackagesLoaderFilesystem::load()
     *
     */
    public function addPackage(array $config): void;

    /**
     * Запуск сбора пакетов
     *
     * @uses PackagesLoaderInterface::load()
     */
    public function load(): void;

    /**
     * Получение списка классов загрузчиков ядра
     *
     * @return array
     */
    public function getBootstraps(): array;

    /**
     * Получение списка подписчиков событий из пакетов
     *
     * @return array[] = ['\Events\EventClassName' => ['\My\Handler1','\Other\Handler3'], //....]
     */
    public function getEventsHandlers(): array;
}
