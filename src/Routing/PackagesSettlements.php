<?php

namespace Symbiotic\Routing;

use Symbiotic\Core\Support\Collection;
use Symbiotic\Laravel\SettlementsRouter\Contracts\SettlementInterface;
use Symbiotic\Packages\PackagesRepositoryInterface;

/**
 * Class Settlements
 * @package Symbiotic\Services
 *

 */
class PackagesSettlements implements SettlementsInterface
{

    /**
     * @var SettlementsInterface
     */
    protected SettlementsInterface $settlements;

    /**
     * @var SettlementFactory
     */
    protected SettlementFactory $factory;

    protected PackagesRepositoryInterface $packages;

    protected string $backend_prefix;


    public function __construct(SettlementsInterface $settlements, PackagesRepositoryInterface $packages, SettlementFactory $factory, string $backend_prefix)
    {
        $this->settlements = $settlements;
        $this->factory = $factory;
        $this->packages = $packages;
        $this->backend_prefix = trim($backend_prefix,"\\/");
    }


    public function getByRouter(string $router)
    {
        return $this->getSettlementByString($router);
    }

    public function getByUrl(string $url): ?Settlement
    {
        $path = $this->getPathByUrl($url);
        return $this->getSettlementByString($path);
    }

    protected function getSettlementByString(string $string): ?Settlement
    {

        //$backend_prefix = \trim($app('config::backend_prefix', 'backend'), '/');
        if (preg_match('~^(backend|api|default):([0-9a-z_\-\.]+)|/('.preg_quote($this->backend_prefix,'~').'|api)/(.[^/]+)~', $string, $m)) {
            if (!empty($m[1]) && !empty($m[2])) {
                $router = $m[1];
                $app_id = $m[2];
            } else {
                $router = $m[3];
                $app_id = $m[4];
            }
            if ($this->packages->has($app_id)) {
                return $this->factory->make([
                    'prefix' => '/' .  ($router === 'backend'?$this->backend_prefix:$router) . '/' . $app_id . '/',
                    'router' => ($router === $this->backend_prefix?'backend':$router). ':' . $app_id
                ]);
            }
        }

        $app_id = explode('/', ltrim($string,'\\/'))[0];
        if(!empty($app_id) && $this->packages->has($app_id)) {
            return $this->factory->make([
                'prefix' => $app_id,
                'router' => $app_id
            ]);
        }

        return null;
    }

    /**
     * @param string $key
     * @param $value
     * @param bool $all
     * @return Collection|Settlement[]|Settlement|null
     */
    public function getByKey(string $key, $value, $all = false)
    {
        /**
         * @param $settlement
         * @return bool
         * @todo ДОПИСАТЬ
         */
        $callback = function ($settlement) use ($key, $value) {
            /**
             * @var Settlement $settlement
             */
            return $settlement->get($key) === $value;
        };
        $result = [];
        foreach ($this->items as $v) {
            if ($v->get($key) === $value) {
                if ($all) {
                    $result[] = $v;
                } else {
                    return $v;
                }

            }
        }
        return $result;
    }

    public static function normalizePrefix(string $prefix): string
    {
        $prefix = trim($prefix, ' \\/');

        return $prefix == '' ? '/' : '/' . $prefix . '/';
    }

    public function getPathByUrl(string $url): string
    {
        return preg_replace('~(^((.+?\..+?)[/])|(^(https?://)?localhost(:\d+)?[/]))(.*)~i', '/', $url);
    }
}