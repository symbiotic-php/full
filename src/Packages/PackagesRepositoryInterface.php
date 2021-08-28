<?php

namespace Symbiotic\Packages;


interface PackagesRepositoryInterface
{

    public function getIds(): array;

    public function has(string $id): bool;

    public function get(string $id): array;

    /**
     * Метод возвращает все конфиги пакетов
     *
     * Если вам нужен массив ID приложений используйте {@see PackagesRepositoryInterface::getIds()}
     * Если вам нужно проверить , что пакет есть используйте has() {@see PackagesRepositoryInterface::has()}
     *
     * не надо делать так:
     *     $packages = $obj->getPackages();
     *     if(isset($packages[$id])){}
     *
     * @return array
     */
    public function getPackages(): array;

    public function addPackagesLoader(PackagesLoaderInterface $loader): void;

    /**
     * Метод для добавления модулей из вне
     *
     * @param array $config
     *
     * @used-by PackagesLoaderInterface::load()
     * @see     PackagesLoaderFilesystem::load()
     *
     * @todo нужен ли? можно же у лоадеров забирать и писать внутри.... если не пригодится, удалю
     * @deprecated
     */
    public function addPackage(array $config): void;

    public function load(): void;

    public function getBootstraps(): array;
}
