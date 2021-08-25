<?php

namespace Dissonance\Routing;

interface SettlementsInterface
{


    /**
     * Settlements constructor.
     *
     * @param array $items = [
     *    [
     *      'prefix' => '/backend/',
     *      'router' => 'backend',
     *       // optional params
     *      'settings' => [],
     *      'locale' => ''...
     *    ],
     *    [
     *      'prefix' => '/api/',
     *      'router' => 'api',
     *       // .....
     *    ],
     *    [
     *      'prefix' => '/module1_baseurl/',
     *      'router' => 'module1',
     *       // .....
     *    ]
     * ];
     *
     *
     *
     */




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