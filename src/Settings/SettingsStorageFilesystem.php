<?php

namespace Symbiotic\Settings;


class SettingsStorageFilesystem implements SettingsStorageInterface
{

    protected string $storage_path;

    public function __construct(string $storage_path)
    {
        $this->storage_path = $storage_path;
        // Тестовые костыли, еще не решено будет ли файловый провайдер в ядре
        if (!is_dir($storage_path)) {
            mkdir($storage_path, 0777, true);
        }
    }

    public function set(string $name, array $data = [])
    {
        return (bool)file_put_contents($this->getFilePath($name), \serialize($data));
    }

    protected function getFilePath(string $name)
    {
        return rtrim($this->storage_path, '/\\') . '/' . md5($name);
    }

    public function get(string $name): array
    {
        if ($this->has($name)) {
            $data = \file_get_contents($this->getFilePath($name));

            $values = \unserialize($data);
            if (!is_array($values)) {
                throw new \Exception('Not unserialize key [' . $name . ']!');
            }

            return $values;
        }
        return [];
    }

    public function has(string $name): bool
    {
        return \is_readable($this->getFilePath($name));
    }

    public function remove(string $name)
    {
        unlink($this->getFilePath($name));
    }
}