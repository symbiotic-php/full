<?php

namespace Symbiotic\Apps;

interface AppsRepositoryInterface
{
    /**
     * @return array
     */
    public function enabled(): array;

    /**
     * @return  array
     */
    public function all(): array;

    /**
     * @return string[]
     */
    public function getIds():array;


    /**
     * @param string $id
     * @return ApplicationInterface | null
     */
    public function get(string $id): ?ApplicationInterface;

    /**
     * @param string $id
     * @return bool
     */
    public function has(string $id);

    /**
     * @param string $id
     * @return ApplicationInterface|null
     */
    public function getBootedApp(string $id): ?ApplicationInterface;

    /**
     * @param string $app_id
     * @return array
     */
    public function getPluginsIds(string $app_id);

    /**
     * @param array $config
     * @return mixed
     */
    public function addApp(array $config);
}