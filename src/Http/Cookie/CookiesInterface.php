<?php

namespace Dissonance\Http\Cookie;

use Psr\Http\Message\ResponseInterface;


interface CookiesInterface extends \ArrayAccess
{
    const COOKIE_HEADER = 'Cookie';
    const SET_COOKIE_HEADER = 'Set-Cookie';

    const SAMESITE_NONE = 'None';
    const SAMESITE_LAX = 'Lax';
    const SAMESITE_STRICT = 'Strict';

    const SAMESITE_VALUES = [
        self::SAMESITE_NONE,
        self::SAMESITE_LAX,
        self::SAMESITE_STRICT
    ];

    /**
     * Set global values for cookies
     *
     * @param string|null $domain
     * @param bool|null $secure
     * @param int|null $expires
     * @param string|null $path
     * @param string|null $same_site
     * @return mixed
     */
    public function setDefaults(string $domain = null, string $path = null, int $expires = null, bool $secure = null, string $same_site = null);

    /**
     * Installing cookies from the request for further work
     *
     * @param array $cookies - [name => value,...]
     * @see     ServerRequestInterface::getCookieParams()
     * @used-by CookiesMiddleware::process()
     */
    public function setRequestCookies(array $cookies);

    /**
     * @return array[]|\ArrayAccess[]
     * @uses \Dissonance\Http\Cookie\Cookies::$items
     * @see  CookiesInterface::setCookie()
     * [
     *   0 => ['name' =>'c_name','value' => 'val','domain' =>'domain.com','path' => '/',..],
     *     ...
     * ]
     */
    public function getResponseCookies(): array;

    /**
     * @param string $name
     * @param string $value
     * @param int|null $expires
     * @param bool|null $httponly
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param array $options
     * set same_site as key , allowed values {@see CookiesInterface::SAMESITE_VALUES}
     * set max_age as key - Max-Age cookie param in value
     * @return array|\ArrayAccess
     */
    public function setCookie(
        string $name,
        string $value = '',
        int $expires = null,
        bool $httponly = null,
        string $path = null,
        string $domain = null,
        bool $secure = null,
        array $options = []
    );

    /**
     * Sending the set cookies to the response headers
     *
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function toResponse(ResponseInterface $response): ResponseInterface;


    /**
     * Checks if an a key is present and not null.
     *
     * @param string|array $name
     * @return bool
     */
    public function has(string $name): bool;

    /**
     * Getting cookies by name
     *
     * @param string $name
     * @param string|null $default
     * @return string|array|null - array if setted 'cookie_name[key]' and getting 'cookie_name'
     * @link  https://www.php.net/manual/ru/function.setcookie.php -  array cookie in doc!
     */
    public function get(string $name, string $default = null);

    /**
     * Setting a cookie with default settings
     *
     * @param string|array $name
     * @param mixed $value
     * @return void
     */
    public function set(string $name, string $value = ''): void;

    /**
     * Deleting cookies, takes a name or an array of names
     *
     * @param string|string[] $names
     * @return mixed
     */
    public function remove($names): void;

}