<?php

namespace Symbiotic\Filesystem\Adapter;



use Symbiotic\Filesystem\FilesystemInterface;

class Local extends AbstractAdapter implements FilesystemInterface {

    protected $writeFlags = LOCK_EX;


    /**
     * @var array
     */
    protected static $permissions = [
        'file' => [
            'public' => 0644,
            'private' => 0600,
        ],
        'dir' => [
            'public' => 0755,
            'private' => 0700,
        ],
    ];

    /**
     * @var array
     */
    protected $permissionMap;

    /**
     * Constructor.
     *
     * @param string $root
     * @param int    $writeFlags
     * @param int    $linkHandling
     * @param array  $permissions
     *
     * @throws \LogicException
     */
    public function __construct($root = '/', $writeFlags = LOCK_EX, array $permissions = [])
    {
        $root = is_link($root) ? realpath($root) : $root;
        $this->permissionMap = array_replace_recursive(static::$permissions, $permissions);
        $this->ensureDirectory($root);

        if ( ! is_dir($root) || ! is_readable($root)) {
            throw new \LogicException('The root path ' . $root . ' is not readable.');
        }

        $this->setPathPrefix($root);
        $this->writeFlags = $writeFlags;

    }

    /**
     * Возвращает нумерованный список файлов директории или false
     * [
     *    0 => '..',
     *    1 => 'file.php',
     *    2 => 'link.php',
     *    ....
     * ] : false
     *
     * @param string $dir_path - Полный путь к директории от корня сервера
     *
     * @return array|false
     */
    public function listDir ($dir_path = '') {
        $files = [];
        if( !funex("scandir")) {
            $h = @opendir($dir_path);
            while(false !== ($filename = @readdir($h))) {
                $files [] = $filename;
            }
        } else {
            $files = @scandir($dir_path);
        }
        return $files;
    }


    /**
     * @param string $dirname
     * @param array $options
     * @return bool
     */
    public function createDir($dirname, array $options = []) {

        $return = $dirname = $this->applyPathPrefix($dirname);

        if (!is_dir($dirname)) {
            if (false === @mkdir($dirname, $this->permissionMap['dir'][isset($options['visibility']) ? $options['visibility'] : 'public'], true)
                || false === is_dir($dirname)) {
                $return = false;
            }
        }
        return $return;
    }

    protected function clearstatcache($path, $flag = false)
    {
        clearstatcache($flag, $path);
    }

    /**
     * Ensure the root directory exists.
     *
     * @param string $dirname directory path
     *
     * @return void
     *
     * @throws \Exception in case the root directory can not be created
     */
    protected function ensureDirectory($dirname)
    {
        if (!is_dir($dirname)) {
            $error = !@mkdir($dirname, $this->permissionMap['dir']['public'], true) ? error_get_last() : [];
            if (!@mkdir($dirname, $this->permissionMap['dir']['public'], true)) {
                $error = error_get_last();
            }
            $this->clearstatcache($dirname);
            if (!is_dir($dirname)) {
                $errorMessage = isset($error['message']) ? $error['message'] : '';
                throw new \Exception(sprintf('Impossible to create the directory "%s". %s', $dirname, $errorMessage));
            }
        }
    }


    /**
     * @param string $path
     * @param string $time
     *
     * @return bool
     */
    public function touch($path, $time) {
        return @touch($path, $time, $time);
    }

    /**
     * @param $from
     * @param $to
     * @param bool $delete_from
     * @return bool
     * @throws \Exception
     */
    public function copy($from, $to, $delete_from = false) {
        if(!$this->has($from)) {
            throw new \Exception($from.' File not Found');
        }
        $from = $this->applyPathPrefix($from);
        $to = $this->applyPathPrefix($to);

        if(!is_dir($from)) {
            $this->ensureDirectory(dirname($to));
            $this->copyThrow($from, $to);
        } else {
            $from = rtrim($from,'\\/').'/';
            $to = rtrim($to,'\\/').'/';
            /** @var \SplFileInfo $file */
            foreach ($this->getRecursiveDirectoryIterator($from,\RecursiveIteratorIterator::CHILD_FIRST) as $file)
            {
                $old_path = ($file->getType() == 'link') ? $file->getPathname() : $file->getRealPath();
                $new_path = str_replace($from, $to, $old_path);

                if(!$file->isDir()) {
                    $this->ensureDirectory(dirname($new_path));
                    $this->copyThrow($old_path, $new_path);
                } else {
                    $this->ensureDirectory($new_path);
                }
            }
        }

        if($delete_from) {
           return $this->delete($from);
        }
        return true;
    }

    protected function copyThrow($path, $newpath)
    {
        if($result = copy($path, $newpath)) {
            return $result;
        }
        throw new \Exception('File not copied : '.$path);
    }

    /**
     * @param $dir_from
     * @param $dir_to
     * @param $filename
     *
     * @return bool
     */
    public function move ($dir_from, $dir_to, $filename) {
        return $this->copy($dir_from, $dir_to, $filename, true);
    }

    /**
     * @param string $path
     * @return bool
     */
    public function delete($path)
    {
        $path = $this->applyPathPrefix($path);
        if(is_dir($path)) {
            return $this->deleteDir($this->removePathPrefix($path));
        }

        return @unlink($path);
    }

    public function deleteDir($path)
    {
        $path = $this->applyPathPrefix($path);
        if(!is_dir($path)) {
            return false;
        }
        /** @var \SplFileInfo $file */
        foreach ($this->getRecursiveDirectoryIterator($path, \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $this->deleteFileInfoObject($file);
        }

        return rmdir($path);
    }


    /**
     * @param \SplFileInfo $file
     */
    protected function deleteFileInfoObject(\SplFileInfo $file)
    {
        switch ($file->getType()) {
            case 'dir':
               return rmdir($file->getRealPath());
                break;
            case 'link':
                return unlink($file->getPathname());
                break;
            default:
                unlink($file->getRealPath());
        }
    }


    /**
     * @param string $path
     *
     * @return \DirectoryIterator
     */
    protected function getDirectoryIterator($path)
    {
        return new \DirectoryIterator($path);
    }
    /**
     * @param string $path
     * @param int    $mode
     * @todo : убрать SPL !!!!! это говно плохо работает!!!!
     * @return \RecursiveIteratorIterator
     */
    public function getRecursiveDirectoryIterator($path, $mode = \RecursiveIteratorIterator::SELF_FIRST)
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            $mode
        );
    }

    /**
     * @param $path
     *
     * @return bool|string
     */
    public function read($path) {
        return @file_get_contents($this->applyPathPrefix($path));
    }

    /**
     * @param $path
     * @param $contents
     * @param $options
     *
     * @return bool|int
     */
    public function write(string $path, $contents, array $options = []) {

        $path = $this->applyPathPrefix($path);
        $time = $this->has($path) ? filemtime($path) : time();

        $result = @file_put_contents($path, $contents, isset($options['flags']) ? $options['flags'] : $this->writeFlags);
        if($result && !empty($options['no_touch'])) {
            @touch($path, $time, $time);
        }

        return $result;
    }

    public function rename($path, $newpath) {
        $path = $this->applyPathPrefix($path);
        $newpath = $this->applyPathPrefix($newpath);

        $this->ensureDirectory(dirname($newpath));

        return rename($path, $newpath);

    }

    /**
     * @inheritdoc
     */
    public function has($path)
    {
        $location = $this->applyPathPrefix($path);

        return file_exists($location);
    }
    /**
     * @param $file_data
     * @param string $format
     *
     * @return bool|float|int|string
     */
    public function getPerms ($file_data, $format = 'oct') {
        if(arkex($format . '_perms', $file_data)) {
            return $file_data[ $format . '_perms' ];
        }
        if( !isar($file_data)) {
            $file_data = $this->fileinfo($file_data);
        }
        return $this->permsFormat(ifset($file_data, 'perms', 0), 'base', $format);
    }

    /**
     * @param $filepath
     * @param string $oct_perms
     *
     * @return bool
     */
    public function setPerms ($filepath, $oct_perms = '') {
        if( !empty($oct_perms)) {
            $pm = 0;
            for($i = strlen($oct_perms)-1; $i >= 0; --$i) {
                $pm += (int) $oct_perms[ $i ]*pow(8, (strlen($oct_perms)-$i-1));
            }
            return chmod($filepath, $pm);
        }
        return false;
    }

    public function listContents($directory = '', $recursive = false)
    {
        // TODO: Implement listContents() method.
    }

    public function getMetadata($path)
    {
        // TODO: Implement getMetadata() method.
    }

    public function getSize($path)
    {
        // TODO: Implement getSize() method.
    }

    public function getMimetype($path)
    {
        // TODO: Implement getMimetype() method.
    }

    public function getTimestamp($path)
    {
        // TODO: Implement getTimestamp() method.
    }

    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.
    }

    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method.
    }


}
