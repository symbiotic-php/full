<?php

namespace Symbiotic\Session;

use Symbiotic\Container\ArrayAccessTrait;

class SessionStorageNative implements SessionStorageInterface
{
    use ArrayAccessTrait;

    protected array $items = [];

    protected bool $started = false;
    /**
     * @var string|null
     */
    protected ?string $session_namespace;

    public function __construct(string $session_namespace = null)
    {
        $this->session_namespace = $session_namespace;
    }

    public function set($key, $value): void
    {
        $this->start();
        $this->items[$key] = $value;
    }

    /**
     * Start the session, reading the data from a handler.
     *
     * @return bool
     */
    public function start()
    {
        if ($this->started) {
            return true;
        }

        if (\PHP_SESSION_ACTIVE !== \session_status()) {
            // ok to try and start the session
            if (!\session_start()) {
                throw new \RuntimeException('Failed to start the session');
            }
        }
        $this->loadSession();
        $this->started = true;
        if (!$this->has('_token')) {
            $this->regenerateToken();
        }

        return true;
    }

    /**
     * Load the session data from the handler.
     *
     * @return void
     */
    protected function loadSession()
    {
        $session_namespace = $this->session_namespace;
        if ($session_namespace) {
            if (!isset($_SESSION[$session_namespace])) {
                $_SESSION[$session_namespace] = [];
            }
            $this->items =  &$_SESSION[$session_namespace];
        } else {
            $this->items =  &$_SESSION;
        }
    }

    public function has(string $key): bool
    {
        $this->start();
        return isset($this->items[$key]); // todo: may be array_key_exists???
    }

    /**
     * Regenerate the CSRF token value.
     *
     * @return string
     */
    public function regenerateToken()
    {
        $this->set('_token', $token = \md5(\uniqid('', true)));
        return $token;
    }

    public function delete(string $key): bool
    {
        $this->start();
        unset($this->items[$key]);
        return true;
    }

    public function clear()
    {
        $this->items = [];
    }

    public function destroy()
    {
        return \session_destroy();
    }

    /**
     * Save the session data to storage.
     *
     * @return bool
     */
    public function save()
    {
        // native save
        return true;
    }

    /**
     * Determine if the session has been started.
     *
     * @return bool
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Get the name of the session.
     *
     * @return string
     */
    public function getName()
    {
        return \session_name();
    }

    /**
     * Get the current session ID.
     *
     * @return string
     */
    public function getId()
    {
        return \session_id();
    }

    /**
     * Set the session ID.
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id)
    {
        if (\session_status() === \PHP_SESSION_ACTIVE || !\ctype_alnum($id) || !\strlen($id) === 40) {
            throw new \Exception('Session active or invalid id');
        }
        \session_id($id);
    }


    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    public function token()
    {
        return !$this->has('_token') ? $this->regenerateToken() : $this->get('_token');
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        $this->start();
        return $this->items[$key] ?? null;
    }

}