<?php

declare(strict_types=1);

namespace Symbiotic\Auth\Authenticator;

use Symbiotic\Auth\AuthResult;
use Symbiotic\Auth\ResultInterface;
use Symbiotic\Session\SessionStorageInterface;


class SessionAuthenticator extends AbstractAuthenticator
{

    const SESSION_USER_KEY = 'symbiotic_user';

    /**
     * @param SessionStorageInterface $session Service without namespace , all $_SESSION array
     *
     * @see SessionAuthProvider::register()
     */
    public function __construct(protected SessionStorageInterface $session)
    {
    }

    /**
     * Performs an authentication attempt
     *
     * @return ResultInterface
     * @throws \Exception If authentication cannot be performed
     */
    public function authenticate(): ResultInterface
    {
        $key = static::SESSION_USER_KEY;
        $session = $this->session;
        if ($session->has($key)) {
            $user = $session->get($key);
            if (\is_array($user)) {
                return new AuthResult($this->initUser($user));
            }
        }
        return (new AuthResult())->setError('Not exists symbiotic user session data!');
    }
}