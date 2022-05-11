<?php

namespace Symbiotic\Settings;


class SettingsRepository implements SettingsRepositoryInterface
{

    /**
     * @var SettingsStorageInterface
     */
    protected SettingsStorageInterface $storage;

    public function __construct(SettingsStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param string $key
     * @param SettingsInterface $settings
     * @return bool
     */
    public function save(string $key, SettingsInterface $settings)
    {
        return $this->storage->set($key, $settings->all());
    }

    /**
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array
    {
        if (!$this->has($key)) {
            return null;
        }
        return $this->storage->get($key);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->storage->has($key);
    }

    public function remove(string $key): bool
    {
        return $this->storage->remove($key);
    }
}