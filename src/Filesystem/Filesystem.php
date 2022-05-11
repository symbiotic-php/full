<?php

namespace Symbiotic\Filesystem;


class Filesystem implements FilesystemInterface
{


    /**
     * @var AdapterInterface
     */
    protected AdapterInterface $adapter;

    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     */
    public function has($path)
    {
        $path = self::normalizePath($path);

        return strlen($path) === 0 ? false : (bool)$this->getAdapter()->has($path);
    }

    public static function normalizePath($path)
    {
        $path = rtrim(str_replace("\\", "/", trim($path)), '/');
        $unx = (strlen($path) > 0 && $path[0] == '/');
        $parts = array_filter(explode('/', $path));
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' === $part) continue;
            if ('..' === $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $path = implode('/', $absolutes);
        $path = $unx ? '/' . $path : $path;

        return $path;
    }

    /**
     * Get the Adapter.
     *
     * @return AdapterInterface adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @inheritdoc
     */
    public function write(string $path, $contents, array $options = [])
    {
        return (bool)$this->getAdapter()->write(self::normalizePath($path), $contents, $options);
    }

    /**
     * @inheritdoc
     */
    public function readAndDelete($path)
    {
        $path = self::normalizePath($path);
        $contents = $this->read($path);
        if ($contents === false) {
            return false;
        }
        $this->delete($path);
        return $contents;
    }

    /**
     * @inheritdoc
     */
    public function read($path)
    {
        return $this->getAdapter()->read(self::normalizePath($path));
    }

    /**
     * @inheritdoc
     */
    public function delete($path)
    {
        return $this->getAdapter()->delete(self::normalizePath($path));
    }

    /**
     * @inheritdoc
     */
    public function rename($path, $newPath)
    {
        return (bool)$this->getAdapter()->rename(self::normalizePath($path), self::normalizePath($newPath));
    }

    /**
     * @inheritdoc
     */
    public function copy($path, $newpath)
    {
        return $this->getAdapter()->copy(self::normalizePath($path), self::normalizePath($newpath));
    }

    /**
     * @inheritdoc
     */
    public function deleteDir($dirname)
    {
        $dirname = self::normalizePath($dirname);

        if ($dirname === '') {
            throw new \Exception('Root directories can not be deleted.');
        }

        return (bool)$this->getAdapter()->deleteDir($dirname);
    }

    /**
     * @inheritdoc
     */
    public function createDir($dirname, array $config = [])
    {
        return (bool)$this->getAdapter()->createDir(self::normalizePath($dirname), $config);
    }

    /**
     * @inheritdoc
     */
    public function listContents($directory = '', $recursive = false)
    {

        return $this->getAdapter()->listContents(self::normalizePath($directory), $recursive);
    }

    /**
     * @inheritdoc
     */
    public function getMimetype($path)
    {
        return $this->getAdapter()->getMimetype(self::normalizePath($path));
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp($path)
    {
        return $this->getAdapter()->getTimestamp(self::normalizePath($path));
    }

    /**
     * @inheritdoc
     */
    public function getVisibility($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        if ((!$object = $this->getAdapter()->getVisibility($path)) || !array_key_exists('visibility', $object)) {
            return false;
        }

        return $object['visibility'];
    }

    /**
     * @inheritdoc
     */
    public function getSize($path)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        if ((!$object = $this->getAdapter()->getSize($path)) || !array_key_exists('size', $object)) {
            return false;
        }

        return (int)$object['size'];
    }

    /**
     * @inheritdoc
     */
    public function setVisibility($path, $visibility)
    {
        $path = Util::normalizePath($path);
        $this->assertPresent($path);

        return (bool)$this->getAdapter()->setVisibility($path, $visibility);
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($path)
    {
        $path = Util::normalizePath($path);
        return $this->getAdapter()->getMetadata($path);
    }
}
