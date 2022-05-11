<?php

namespace Symbiotic\Routing;

interface SettlementsInterface
{

    public function getByRouter(string $router);

    public function getByUrl(string $url) : ? Settlement;


    /**
     * @param string $key
     * @param $value
     * @param bool $all
     * @return Settlement[]|Settlement|null
     */
    public function getByKey(string $key, $value, $all = false);


}