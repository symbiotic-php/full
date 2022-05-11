<?php

/**
 * @see       https://github.com/laminas/laminas-authentication for the canonical source repository
 * @copyright https://github.com/laminas/laminas-authentication/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-authentication/blob/master/LICENSE.md New BSD License
 */

namespace Symbiotic\Auth\Authenticator;


use Symbiotic\Auth\AuthenticatorInterface;
use Symbiotic\Auth\User;
use Symbiotic\Auth\UserInterface;

abstract class AbstractAuthenticator implements AuthenticatorInterface
{
    protected function initUser(array $data): UserInterface
    {
        if (!isset($data['access_group'])) {
            throw new \Exception("The user's group is not defined!");
        }
        return new User(
            (int)$data['access_group'],
            $data['full_name'] ?? $this->getDefaultUserName($data['access_group']),
            $data['id'] ?? null
        );
    }

    protected function getDefaultUserName(int $accessGroup)
    {
        $fullName = 'No Name';
        switch ($accessGroup) {
            case UserInterface::GROUP_GUEST:
                $fullName = 'Guest';
                break;
            case UserInterface::GROUP_MANAGER:
                $fullName = 'Manager';
                break;
            case UserInterface::GROUP_ADMIN:
                $fullName = 'Admin';
                break;
        }
        return $fullName;

    }
}