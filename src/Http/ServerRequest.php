<?php

namespace Symbiotic\Http;

/**
 * Class ServerRequest
 */
class ServerRequest extends \Nyholm\Psr7\ServerRequest
{

    public function isXMLHttpRequest()
    {
        return $this->getServerParam('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';
    }

    public function getUserAgent()
    {
        return $this->getServerParam('HTTP_USER_AGENT');
    }

    public function getServerParam($name, $default = null)
    {
        $server = $this->getServerParams();
        return isset($server[$name]) ? $server[$name] : $default;

    }

    /**
     * @param $name
     * @param null| string $default
     */
    public function getInput($name, $default = null)
    {
       $params =  $this->getParsedBody();
        return $params[$name] ?? $default;
    }

    public function getQuery($name, $default = null)
    {
        $params = $this->getQueryParams();
        return $params[$name] ?? $default;
    }

}