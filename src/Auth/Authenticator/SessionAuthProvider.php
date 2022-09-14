<?php

declare(strict_types=1);

namespace Symbiotic\Auth\Authenticator;

use Psr\Container\ContainerInterface;
use Symbiotic\Core\ServiceProvider;
use Symbiotic\Session\SessionManager;
use Symbiotic\Session\SessionManagerInterface;


class SessionAuthProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->afterResolving(
            MultiAuthenticator::class,
            static function (MultiAuthenticator $authenticator, ContainerInterface $app) {
                /**
                 * @var SessionManager $sessionManager
                 */
                $sessionManager = $app->get(SessionManagerInterface::class);
                $session = $sessionManager->createNativeDriver([], $app);
                $authenticator->addAuthenticator(
                    new SessionAuthenticator($session)
                );
            }
        );
    }
}