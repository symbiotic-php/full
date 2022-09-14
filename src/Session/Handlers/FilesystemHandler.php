<?php

declare(strict_types=1);

namespace Symbiotic\Session\Handlers;

use Symbiotic\Filesystem\AdapterInterface;
use Symbiotic\Filesystem\FilesystemManagerInterface;


class FilesystemHandler implements \SessionHandlerInterface
{

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected string $path;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected int $minutes;

    /**
     * @var AdapterInterface
     */
    private AdapterInterface $files;

    /**
     * Create a new file driven handler instance.
     *
     * @param FilesystemManagerInterface $filesystemManager
     * @param string                     $path
     * @param int                        $minutes
     *
     */
    public function __construct(FilesystemManagerInterface $filesystemManager, string $path, int $minutes)
    {
        $this->files = $filesystemManager->createLocalDriver(['root' => '']);
        $this->path = $path;
        $this->minutes = $minutes;
    }

    /**
     * {@inheritdoc}
     * for generation custom sub dirs
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return string|false
     */
    public function read(string $id): string|false
    {
        $path = $this->path . \_S\DS . $id;
        if ($this->files->has($path) &&
            $this->files->getMTime($path) >= time() - (60 * $this->minutes)) {
            return $this->files->read($path, LOCK_SH);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function write(string $id, string $data): bool
    {
        return !empty($this->files->write($this->path . \_S\DS . $id, $data, ['flags' => LOCK_EX]));
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function destroy(string $id): bool
    {
        $this->files->delete($this->path . \_S\DS . $id);

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function gc(int $max_lifetime): int|false
    {
        if (!\is_dir($this->path) || !\is_readable($this->path)) {
            return false;
        }
        $deleted = 0;
        /**
         * @var \SplFileInfo $file
         */
        foreach (new \DirectoryIterator($this->path) as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($file->getMTime() <= (time() - $max_lifetime)) {
                $this->files->delete($file->getRealPath());
                $deleted++;
            }
        }

        return $deleted;
    }
}
