<?php


namespace Symbiotic\Auth\Authenticator;


use Symbiotic\Core\ServiceProvider;
use Symbiotic\Session\SessionStorageNative;

class SessionAuthProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(MultiAuthenticator::class, function (MultiAuthenticator $authenticator) {
            $authenticator->addAuthenticator(
                new SessionAuthenticator(new SessionStorageNative())
            );
        });
    }
}