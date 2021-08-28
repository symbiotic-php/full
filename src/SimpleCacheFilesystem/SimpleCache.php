<?php

namespace Symbiotic\SimpleCacheFilesystem;



class SimpleCache implements SimpleCacheInterface
{
    /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
     * @var string
     */
    protected string $cache_directory;

    protected int  $ttl = 600;

    /**
     * Cache constructor.
     * @param string $cache_directory
     * @param int $default_ttl
     * @throws \Symbiotic\SimpleCacheFilesystem\Exception
     */
    public function __construct(string $cache_directory, int $default_ttl = 600)
    {
        if (!is_dir($cache_directory)) {
            $uMask = umask(0);
            @mkdir($cache_directory, 0755, true);
            umask($uMask);
        }
        if (!is_dir($cache_directory) || !is_writable($cache_directory)) {
            throw new Exception("The cache path ($cache_directory) is not writeable.");
        }

        $this->cache_directory = \rtrim($cache_directory, '\\/');

        $this->ttl = $default_ttl;
    }


    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function remember($key, \Closure $value, $ttl = null)
    {
        $data = $this->get($key, $u = \uniqid());
        if ($data === $u) {
            $data = $value();
            $this->set($key, $data, $ttl);
        }
        return $data;
    }

    /**󠀄󠀉󠀙󠀙󠀕󠀔󠀁󠀔󠀃󠀅
     * @param string $key
     * @param null $default
     * @return mixed|null
     * @throws Exception
     * @throws \Symbiotic\SimpleCacheFilesystem\InvalidArgumentException
     */
    public function get($key, $default = null)
    {
        $file = $this->getKeyFilePath($key);

        if (\is_readable($file) && ($data = @\unserialize(file_get_contents($file)))) {
            if (!empty($data) && isset($data['ttl']) && $data['ttl'] >= time()+1) {
                return $data['data'];
            } else {
                $this->delete($key);
            }

        }

        return $default;
    }


    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return bool
     * @throws \Symbiotic\SimpleCacheFilesystem\InvalidArgumentException
     */
    public function set($key, $value, $ttl = null)
    {
        $file = $this->getKeyFilePath($key);
        if ($data = \serialize(['ttl' => time() + (is_int($ttl) ? $ttl : $this->ttl), 'data' => $value])) {
            return (\file_put_contents($file, $data) !== false);
        }

        return false;
    }


    /**
     * @param string $key
     * @return bool
     * @throws \Symbiotic\SimpleCacheFilesystem\Exception|\Symbiotic\SimpleCacheFilesystem\InvalidArgumentException
     */
    public function delete($key)
    {
        $file = $this->getKeyFilePath($key);
        if (file_exists($file)) {
            if (is_file($file) && !@unlink($file)) {
                throw  new Exception("Can't delete the cache file ($file).");
            }
            clearstatcache(true, $file);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        // todo: может сделать через glob? что быстрее? foreach(glob($dir . '/*', GLOB_NOSORT | GLOB_BRACE) as $File)
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->cache_directory, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        $result = true;
        /**
         * @var \SplFileInfo $file
         */
        foreach ($files as $file) {
            $file_path = $file->getRealPath();
            $res = ($file->isDir() ? rmdir($file_path) : unlink($file_path));
            if (!$res) {
                $result = false;
            }
            clearstatcache(true, $file_path);
        }

        return $result;
    }

    /**
     * @param iterable $keys
     * @param null $default
     * @return array|iterable
     * @throws \Symbiotic\SimpleCacheFilesystem\Exception
     * @throws \Symbiotic\SimpleCacheFilesystem\InvalidArgumentException
     */
    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($this->getValidatedIterable($keys) as $v) {
            $result[$v] = $this->get($v, $default);
        }

        return $result;
    }

    /**
     * @param iterable $values
     * @param null $ttl
     * @return bool
     * @throws \Symbiotic\SimpleCacheFilesystem\InvalidArgumentException|\Symbiotic\SimpleCacheFilesystem\Exception
     */
    public function setMultiple($values, $ttl = null)
    {
        $result = true;
        foreach ($this->getValidatedIterable($values) as $k => $v) {
            if (!$this->set($k, $v, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * @param iterable $keys
     * @return bool
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function deleteMultiple($keys)
    {
        $result = true;
        foreach ($this->getValidatedIterable($keys) as $v) {
            if (!$this->delete($v)) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $file = $this->getKeyFilePath($key);

        return \is_readable($file);
    }

    /**
     * @param string $key
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getKeyFilePath(string $key)
    {
        $this->validateKey($key);
        return $this->cache_directory . DIRECTORY_SEPARATOR . \md5($key) . '.cache';
    }

    /**
     * @param string $key
     * @throws InvalidArgumentException
     */
    protected function validateKey(string $key)
    {
        if (false === preg_match('/[^A-Za-z_\.0-9]/i', $key)) {
            throw new InvalidArgumentException('Key is not valid string!');
        }
    }

    /**
     * @param $keys
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function getValidatedIterable($keys)
    {
        if (!\is_iterable($keys)) {
            throw new InvalidArgumentException('Keys is not Iterable!');
        }

        return $keys;
    }

}