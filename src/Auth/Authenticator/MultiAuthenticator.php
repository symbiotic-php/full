<?php

namespace Symbiotic\Auth\Authenticator;

use Symbiotic\Auth\AuthenticatorInterface;
use Symbiotic\Auth\AuthResult;
use Symbiotic\Auth\ResultInterface;


class MultiAuthenticator implements AuthenticatorInterface
{
    protected array $authenticators = [];

    public function addAuthenticator(AuthenticatorInterface $authenticator, $prepend = false)
    {
        if ($prepend) {
            array_unshift($this->authenticators, $authenticator);
        } else {
            $this->authenticators[] = $authenticator;
        }
    }

    public function authenticate():ResultInterface
    {
        foreach ($this->authenticators as $authenticator) {
            $result = $authenticator->authenticate();
            if ($result->isValid()) {
                return $result;
            }
        }
        return (new AuthResult())->setError('Not auth!');
    }
}