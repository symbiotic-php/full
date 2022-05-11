<?php

/**
 * @see       https://github.com/laminas/laminas-authentication for the canonical source repository
 * @copyright https://github.com/laminas/laminas-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-authentication/blob/master/LICENSE.md New BSD License
 */

namespace Symbiotic\Auth;

interface AuthenticatorInterface
{


    /**
     * Performs an authentication attempt
     *
     * @return ResultInterface
     * @throws \Exception If authentication cannot be performed
     */
    public function authenticate(): ResultInterface;
}