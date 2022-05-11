<?php


namespace Symbiotic\Auth\Storage;

use Symbiotic\Auth\AuthStorageInterface;
use Symbiotic\Session\SessionStorageInterface;


class AuthSessionStorage implements AuthStorageInterface
{
    const DATA_KEY = 'auth_user';
    /**
     * @var SessionStorageInterface
     */
    protected SessionStorageInterface $session;

    public function __construct(SessionStorageInterface $storage)
    {
        $this->session = $storage;
    }
    /**
     * Returns true if and only if storage is empty
     *
     * @return bool
     */
    public function isEmpty():bool
    {
        return empty($this->session->get(static::DATA_KEY));
    }

    /**
     * Returns the contents of storage
     * Behavior is undefined when storage is empty.
     *
     * @return string|null
     */
    public function read()
    {
        return $this->session->get(static::DATA_KEY);
    }

    /**
     * Writes $contents to storage
     *
     * @param  mixed $contents
     * @return void
     */
    public function write($contents)
    {
        $this->session->set(static::DATA_KEY, $contents);
    }

    /**
     * Clears contents from storage
     *
     * @return void
     */
    public function clear()
    {
        $this->session->delete(static::DATA_KEY);
    }
}