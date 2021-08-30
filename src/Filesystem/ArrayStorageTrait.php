<?php


namespace Symbiotic\Filesystem;



trait ArrayStorageTrait
{
    private $storage_path = null;

    protected function setStoragePath(string $path)
    {
        $path = \rtrim($path);
        if(!file_exists($path)) {
            mkdir($path,0700,true);
        }
        if(!is_dir($path)) {
            throw new NotExistsException("Не удалось создать папку[$path]!");
        }
        $this->storage_path = $path;
    }

    /**
     * @param string $fileName
     * @param callable $callback
     *
     * @return array
     * @throws \Exception
     */
    public function remember(string $fileName, callable $callback)
    {
        if(!$this->storage_path) {
            return $callback();
        }
         $path = $this->storage_path.\_DS\DS.$fileName;
         if(\is_readable($path)) {
             return include $path;
         }
         $data = $callback();
         // TODO: может нул разрешить?
         if(!\is_array($data)) {
             throw new \TypeError('Данные должны быть массивом ['.gettype($data).']!');
         }
         if(!\file_put_contents($path,'<?php '.PHP_EOL.'return '.var_export($data,true).';')){
             throw new \Exception('Не удалось записать в файл['.$path.']!');
         }
         return $data;
    }
}

