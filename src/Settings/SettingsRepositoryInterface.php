<?php

namespace Symbiotic\Settings;


interface SettingsRepositoryInterface
{
    /**
     * @param string $key
     * @param SettingsInterface $settings
     * @return bool
     */
    public function save(string $key, SettingsInterface $settings);

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key):bool;

    /**
     * @param string $key
     * @return array|null
     */
    public function get(string $key):?array;

    /**
     * @param string $key
     * @return bool
     */
    public function remove(string $key):bool;
}