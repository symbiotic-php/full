<?php

namespace Symbiotic\Auth;

/**
 * @copyright 2020 laminas Project
 * @author laminas Project
 * @refactoring Symbiotic
 * @license   BSD-3-Clause
 *
 * Provides an API for authentication and identity management
 */
interface AuthServiceInterface
{

    /**
     * Authenticates and provides an authentication result
     *
     * @param AuthenticatorInterface|null $adapter
     * @return ResultInterface
     */
    public function authenticate(AuthenticatorInterface $adapter = null): ResultInterface;

    /**
     * Returns true if and only if an identity is available
     *
     * @return bool
     */
    public function hasIdentity(): bool;

    /**
     * Returns the authenticated identity or null if no identity is available
     *
     * @return mixed|null
     * @throws
     */
    public function getIdentity();

    /**
     * Clears the identity
     *
     * @return void
     */
    public function clearIdentity();


}