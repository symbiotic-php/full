<?php

namespace Dissonance\Http\Cookie;

use Psr\Http\Message\ResponseInterface;

class Cookies implements CookiesInterface
{

    /**
     * @var array[]
     */
    protected $items = [];

    /**
     * @var string|null
     */
    protected $domain;

    /**
     * @var string if empty path, browser set request request path
     */
    protected $path = '';

    /**
     * @var bool
     */
    protected $secure = false;

    /**
     * @var int
     */
    protected $expires = 0;

    /**
     * @var string|null
     * @uses \Dissonance\Http\Cookie\CookiesInterface::SAMESITE_VALUES
     */
    protected $same_site;

    /**
     * @var array [ name => value...]
     */
    protected $request_cookies = [];


    /**
     * @param string|null $domain
     * @param string|null $path
     * @param int|null $expires
     * @param bool|null $secure
     * @param string|null $same_site
     * @return mixed|void
     * @throws \Exception
     */
    public function setDefaults(string $domain = null, string $path = null, int $expires = null, bool $secure = null, string $same_site = null)
    {
        if ($domain) {
            $this->domain = $domain;
        }
        if (!is_null($secure)) {
            $this->secure = $secure;
        }
        if (is_int($expires)) {
            $this->expires = $expires;
        }
        if ($path) {
            $this->path = $path;
        }
        if ($same_site) {
            if (!in_array($same_site, static::SAMESITE_VALUES)) {
                throw new \Exception('Incorrect sameSite value(' . $same_site . ')');
            }
            $this->same_site = $same_site;
        }
    }

    /**
     * Set cookie to response
     *
     * @notice Please do not install serialized objects, this violates security!!! use json_encode
     * @param string $name
     * @param string $value
     * @param int|null $expires
     * @param bool|null $httponly
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param array $options
     * @return array|\ArrayAccess
     *
     */
    public function setCookie(string $name, string $value = '', int $expires = null, bool $httponly = null, string $path = null, string $domain = null, bool $secure = null, array $options = [])
    {
        $data = [
            'expires' => is_int($expires) ? $expires : $this->expires,
            'httponly' => !empty($httponly),
            'domain' => !is_null($domain) ? $domain : $this->domain,
            'path' => is_null($path) ? $this->path : $path,
            'secure' => !is_null($secure) ? $secure : $this->secure,
        ];
        if (!isset($options['same_site']) && isset($this->same_site)) {
            $options['same_site'] = $this->same_site;
        }
        $data = array_merge($data, $options);
        $cookie = $this->create($name, $value);
        foreach ($data as $k => $v) {
            $cookie[$k] = $v;
        }

        return $this->items[] = $cookie;

    }

    /**
     * @param $name
     * @param string $value
     * @return array
     */
    protected function create($name, $value = '')
    {
        return [
            'name' => $name,
            'value' => $value
        ];
    }

    /**
     * @param array $cookies [ name => value...]
     */
    public function setRequestCookies(array $cookies)
    {
        $this->request_cookies = $cookies;
    }

    /**
     * @inheritDoc
     * @return array|\ArrayAccess[]
     */
    public function getResponseCookies(): array
    {
        return $this->items;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function set(string $name, string $value = ''): void
    {
        $this->setCookie($name, $value);
    }

    /**
     * @inheritDoc
     */
    public function has(string $name): bool
    {
        return isset($this->request_cookies[$name]);
    }

    /**
     * @inheritDoc
     */
    public function get(string $name, string $default = null)
    {
        $cookies = $this->request_cookies;
        return isset($cookies[$name]) ? $cookies[$name] : $default;
    }

    /**
     * Delete cookie
     *
     * @param string[]|string $names
     */
    public function remove($names): void
    {
        foreach ((array)$names as $v) {
            $this->setCookie($v, '', time() - (3600 * 48), true, $this->path, $this->domain);
        }
    }


    /**
     * Send cookies to response
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function toResponse(ResponseInterface $response): ResponseInterface
    {
        foreach ($this->items as $cookie) {
            $response = $response->withAddedHeader(static::SET_COOKIE_HEADER, $this->cookieToResponse($cookie));
        }

        return $response;
    }

    /**
     * Get cookie header value from array
     *
     * @param array | \ArrayAccess $cookie
     * @return string
     */
    public function cookieToResponse($cookie)
    {
        return sprintf('%s=%s; ', $cookie['name'], urlencode($cookie['value']))
            . (!empty($cookie['domain']) ? 'Domain=' . $cookie['domain'] . '; ' : '')
            . (!empty($cookie['path']) ? 'Path=' . $cookie['path'] . '; ' : '')
            . (isset($cookie['expires']) && $cookie['expires'] !== 0 ? sprintf('Expires=%s; ', gmdate('D, d M Y H:i:s T', $cookie['expires'])) : '')
            . (isset($cookie['max_age']) && is_int($cookie['max_age']) ? sprintf('Max-Age=%d; ', $cookie['max_age']) : '')
            . (!empty($cookie['secure']) ? 'Secure; ' : '')
            . (!empty($cookie['httponly']) ? 'HttpOnly; ' : '')
            . (!empty($cookie['same_site']) && in_array($cookie['same_site'], CookiesInterface::SAMESITE_VALUES) ? 'SameSite=' . $cookie['same_site'] . '; ' : '');
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     * @return string or null if not exists
     * @uses get()
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string|array $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }


}