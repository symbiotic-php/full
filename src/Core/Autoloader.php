<?php

namespace Dissonance\Core;

/**
 *
 * ПОЛНЫЙ ГОВНО КОД, но работает )) подделка автолоадера композера(made in china)
 * Class Autoloader
 * @package Dissonance
 */
class Autoloader
{

    protected static $namespace = 'Dissonance';

    private static $registered = false;

    protected static $packages_dirs = [];

    protected static $strorage_path = null;

    protected static $registered_namespaces = [];

    protected static $files = [];

    protected static $classes = [];


    static public function register($prepend = false, array $scan_dirs = null, $storage_path = null)
    {
        if($storage_path) {
            self::$strorage_path = rtrim($storage_path,'/\\');
        }

        if (self::$registered === true) {
            return;
        }
        self::$packages_dirs = $scan_dirs ? array_map(function ($v) {
            return rtrim($v, '\\/');
        }, $scan_dirs) : [];
        spl_autoload_register(array(__CLASS__, 'autoload'), true, $prepend);

        self::registerNamespaces();
        self::$registered = true;
    }

    /**
     * @return bool
     */
    public static function registerNamespaces()
    {
        $file = self::$strorage_path ? self::$strorage_path.'/autoload.dump.php':null;
        if (file_exists($file)) {
            $data = include $file;
            static::$registered_namespaces = $data['namespaces'];
            static::$classes = $data['classes'];
            self::requireFiles($data['files']);
            return;
        }
        foreach (self::$packages_dirs as $dirname) {
            if (is_dir($dirname)) {
                static::loadPackages($dirname);
            }
        }
        uksort(static::$registered_namespaces, function ($a, $b) {
            $ex_a = count(explode('\\', trim($a, '/\\')));
            $ex_b = count(explode('\\', trim($b, '/\\')));
            if ($ex_a > $ex_b) {
                return -1;
            } elseif ($ex_a < $ex_b) {
                return 1;
            }
            return 0;
        });
        if($file) {
            if(!is_dir(self::$strorage_path)) {
                \mkdir(self::$strorage_path,0777,true);
            }
            file_put_contents($file, '<?php '.PHP_EOL.'return '.var_export(['namespaces' => static::$registered_namespaces,
                    'classes' => static::$classes, 'files' => static::$files],true).';');
        }
    }

    protected static function loadPackages($dir)
    {
        /*foreach(new \DirectoryIterator($dir) as $fileInfo) {*/

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_SELF));
        $iterator->setMaxDepth(2);
        foreach ($iterator as $fileInfo) {
            if (!$fileInfo->isDir()) continue;

            $composer_file = $fileInfo->getRealPath() . '/composer.json';
            if (\is_readable($composer_file)) {
                $loader = self::getComposerLoader($fileInfo->getRealPath() . '/composer.json', $fileInfo->getRealPath());

                if (!empty($loader)) {
                    // files load now!
                    if (!empty($loader['files'])) {
                        static::$files = array_merge(static::$files, $loader['files']);
                        self::requireFiles($loader['files']);
                    }

                    if (!empty($loader['namespaces'])) {
                        foreach ($loader['namespaces'] as $namespace => $data) {
                            $namespace = trim($namespace, '\\') . '\\';
                            if (array_key_exists($namespace, self::$registered_namespaces)) {
                                $data['root_dir'] = array_merge(self::$registered_namespaces[$namespace]['root_dir'], $data['root_dir']);

                            }
                            self::$registered_namespaces[$namespace] = $data;

                            foreach ($data['root_dir'] as $d) {
                                $directory = new \RecursiveDirectoryIterator($d);
                                $iterator = new \RecursiveIteratorIterator($directory);
                                $regex = new \RegexIterator($iterator, '/\.php$/i');


                                foreach ($regex as $file) {
                                    $path = $file->getRealPath();
                                    $r= preg_replace('@/@',DIRECTORY_SEPARATOR, $d);
                                    $class_path = str_replace($r, '', $path);
                                    $key = $namespace . trim(str_replace('.php', '', $class_path), '\\/');
                                    static::$classes[$key] = $path;
                                }
                            }
                        }
                    }
                }
            }

        }
    }


    protected static function requireFiles($files)
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    protected static function getComposerLoader($file, $base_dir)
    {
        $loader = [];
        if (\is_readable($file)) {
            $data = \json_decode(file_get_contents($file), true);
            if (is_array($data)) {
                $loader['namespaces'] = [];
                $loader['files'] = [];
                $get_autoloads = function ($base_dir, $autoload, array &$loader) {
                    if (isset($autoload['psr-4']) && is_array($autoload['psr-4'])) {
                        foreach ($autoload['psr-4'] as $namespace => $dir) {
                            $namespace = rtrim($namespace, '\\');
                            $loader['namespaces'][$namespace] = [
                                'namespace' => $namespace,
                                'root_dir' => [$base_dir . DIRECTORY_SEPARATOR . trim($dir, '\\/')],

                            ];
                        }
                    }
                    if (isset($autoload['files']) && is_array($autoload['files'])) {
                        foreach ($autoload['files'] as $v) {
                            $loader['files'][] = $base_dir . DIRECTORY_SEPARATOR . ltrim($v, '\\/');
                        }
                    }
                };
                if (isset($data['autoload'])) {
                    $get_autoloads($base_dir, $data['autoload'], $loader);
                }

               /* if (self::$env === 'dev') {
                    if (isset($data['autoload-dev'])) {
                        $get_autoloads($base_dir, $data['autoload-dev'], $loader);
                    }
                }*/
            }
        }

        return $loader;
    }


    static public function autoload($class)
    {
        if (isset(static::$classes[$class])) {

           return static::requireFile(static::$classes[$class]);
        }

       static::search($class);
    }

    protected static function search($class)
    {
        foreach (self::$registered_namespaces as $namespace => $data) {
            if (strpos($class, $namespace) === 0) {
                $name = substr($class, strlen($namespace));

                foreach ($data['root_dir'] as $root_dir) {
                    if (preg_match('/\\\\Tests\\\\$/i', $namespace) /*|| strcasecmp(substr($class, -4), 'Test')*/) {
                        $root_dir = rtrim($root_dir . '/' . $namespace, '\\/');
                        // $name =  $class;
                    }
                    $fileName = strtr($root_dir . '/' . ltrim($name, '\\/'), '\\', '/') . '.php';
                    if (static::requireFile($fileName, false)) {
                        return;
                    } else {
                        $data = $fileName;
                    }
                }
            }
        }
    }
    private static function requireFile($file, $throw = false)
    {
        if (file_exists($file)) {
            return require_once $file;
        } elseif ($throw) {
            debug_print_backtrace(1, 5);
            echo 'File not found' . $file;
            var_dump($file);
        }
        return false;
    }
}
