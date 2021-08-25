<?php

namespace Dissonance\Routing;

use Dissonance\Core\Support\Collection;
use Dissonance\Packages\PackagesRepositoryInterface;

/**
 * Class Settlements
 * @package Dissonance\Services
 *

 */
class PackagesSettlements implements SettlementsInterface
{

    /**
     * @var SettlementsInterface
     */
    protected $settlements;

    /**
     * @var SettlementFactory
     */
    protected $factory;

    protected $packages;


    public function __construct(SettlementsInterface $settlements, PackagesRepositoryInterface $packages, SettlementFactory $factory)
    {
        $this->settlements = $settlements;
        $this->factory = $factory;
        $this->packages = $packages;
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

        if (preg_match('~^(backend|api|default):([0-9a-z_\-\.]+)|/(backend|api|default)/(.[^/]+)~', $string, $m)) {
            if (!empty($m[1]) && !empty($m[2])) {
                $router = $m[1];
                $app_id = $m[2];
            } else {
                $router = $m[3];
                $app_id = $m[4];
            }
            if ($this->packages->has($app_id)) {
                return $this->factory->make([
                    'prefix' => '/' . $router . '/' . $app_id . '/',
                    'router' => $router . ':' . $app_id
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