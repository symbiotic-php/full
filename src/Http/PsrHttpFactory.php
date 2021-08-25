<?php

namespace Dissonance\Http;


use Psr\Http\Message\{
    RequestFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    StreamFactoryInterface,
    UriFactoryInterface,

    RequestInterface,
    ResponseInterface,
    ServerRequestInterface,
    StreamInterface,
    UploadedFileInterface,
    UriInterface,
};


class PsrHttpFactory implements
    UriFactoryInterface,
    StreamFactoryInterface,
    ResponseFactoryInterface,
    ServerRequestFactoryInterface,
    RequestFactoryInterface
{
    /**
     * @return ServerRequestInterface
     */
    public function createServerRequestFromGlobals()
    {
        $server = $_SERVER;
        $method = $server['REQUEST_METHOD'];
        $serverRequest = $this->createServerRequest($method, $this->createUriFromGlobals(), $server);
        foreach ($server as $key => $value) {
            if ($value) {
                if (0 === \strpos($key, 'HTTP_')) {
                    $name = \strtr(\strtolower(\substr($key, 5)), '_', '-');
                    if (\is_int($name)) {
                        $name = (string)$name;
                    }
                    $serverRequest->withAddedHeader((string)$name, $value);
                } elseif (0 === \strpos($key, 'CONTENT_')) {
                    $name = 'content-' . \strtolower(\substr($key, 8));
                    $serverRequest->withAddedHeader($name, $value);

                }
            }

        }

        $serverRequest = $serverRequest
            ->withProtocolVersion(isset($server['SERVER_PROTOCOL']) ? \str_replace('HTTP/', '', $server['SERVER_PROTOCOL']) : '1.1')
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withUploadedFiles($this->normalizeFiles($_FILES));
        if ($method === 'POST') {
            $serverRequest = $serverRequest->withParsedBody($_POST);
        }
        $body = \fopen('php://input', 'r');
        if (!$body) {
            return $serverRequest;
        }
        if (\is_resource($body)) {
            $body = $this->createStreamFromResource($body);
        } elseif (\is_string($body)) {
            $body = $this->createStream($body);
        } elseif (!$body instanceof StreamInterface) {
            throw new \InvalidArgumentException('The $body parameter to ServerRequestCreator::fromArrays must be string, resource or StreamInterface');
        }

        return $serverRequest->withBody($body);
    }

    /**
     * Return an UploadedFile instance array.
     *
     * @param array $files A array which respect $_FILES structure
     *
     * @return UploadedFileInterface[]
     *
     * @throws \InvalidArgumentException for unrecognized values
     */
    private function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (\is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = $this->createUploadedFileFromSpec($value);
            } elseif (\is_array($value)) {
                $normalized[$key] = $this->normalizeFiles($value);
            } else {
                throw new \InvalidArgumentException('Invalid value in files specification');
            }
        }

        return $normalized;
    }

    /**
     * Create and return an UploadedFile instance from a $_FILES specification.
     *
     * If the specification represents an array of values, this method will
     * delegate to normalizeNestedFileSpec() and return that return value.
     *
     * @param array $value $_FILES struct
     *
     * @return array|UploadedFileInterface
     */
    private function createUploadedFileFromSpec(array $value)
    {
        if (\is_array($value['tmp_name'])) {
            return $this->normalizeNestedFileSpec($value);
        }

        try {
            $stream = $this->createStreamFromFile($value['tmp_name']);
        } catch (\RuntimeException $e) {
            $stream = $this->createStream();
        }

        return $this->createUploadedFile(
            $stream,
            (int)$value['size'],
            (int)$value['error'],
            $value['name'],
            $value['type']
        );
    }

    /**
     * Normalize an array of file specifications.
     *
     * Loops through all nested files and returns a normalized array of
     * UploadedFileInterface instances.
     *
     * @return UploadedFileInterface[]
     */
    private function normalizeNestedFileSpec(array $files = []): array
    {
        $normalizedFiles = [];

        foreach (\array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key],
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
            ];
            $normalizedFiles[$key] = $this->createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }

    public function isSecure()
    {
        $server = $_SERVER;
        foreach ([
                     'HTTPS' => ['on', '1'],
                     'HTTP_SSL' => ['1'],
                     'HTTP_X_SSL' => ['yes', '1'],
                     'HTTP_X_FORWARDED_PROTO' => ['https'],
                     'HTTP_X_SCHEME' => ['https'],
                 ] as $key => $values) {
            if (!empty($server[$key])) {
                foreach ($values as $value) {
                    if (strtolower($server[$key]) == $value) {
                        return true;
                    }
                }
            }
        }
        return (!empty($server['HTTP_X_HTTPS']) && strtolower($server['HTTP_X_HTTPS']) != 'off');
    }

    /**
     * Implemented Nyholm/psr7-server
     *
     * @return UriInterface
     */
    public function createUriFromGlobals()
    {
        $uri = $this->createUri('');
        $server = $_SERVER;
        $uri = $uri->withScheme($this->isSecure() ? 'https' : 'http');

        if (isset($server['REQUEST_SCHEME']) && isset($server['SERVER_PORT'])) {
            $uri = $uri->withPort($server['SERVER_PORT']);
        }

        if (isset($server['HTTP_HOST'])) {
            if (1 === \preg_match('/^(.+)\:(\d+)$/', $server['HTTP_HOST'], $matches)) {
                $uri = $uri->withHost($matches[1])->withPort($matches[2]);
            } else {
                $uri = $uri->withHost($server['HTTP_HOST']);
            }
        } elseif (isset($server['SERVER_NAME'])) {
            $uri = $uri->withHost($server['SERVER_NAME']);
        }

        if (isset($server['REQUEST_URI'])) {
            $uri = $uri->withPath(\current(\explode('?', $server['REQUEST_URI'])));
        }

        if (isset($server['QUERY_STRING'])) {
            $uri = $uri->withQuery($server['QUERY_STRING']);
        }

        return $uri;
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri, [], null, '1.1', $serverParams);
    }

    public function createRequest(string $method, $uri): RequestInterface
    {
        return new Request($method, $uri);
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if (2 > \func_num_args()) {
            // This will make the Response class to use a custom reasonPhrase
            $reasonPhrase = null;
        }

        return new Response($code, [], null, '1.1', $reasonPhrase);
    }

    public function createStream(string $content = ''): StreamInterface
    {
        return Stream::create($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = @\fopen($filename, $mode);
        if (false === $resource) {
            if ('' === $mode || false === \in_array($mode[0], ['r', 'w', 'a', 'x', 'c'])) {
                throw new \InvalidArgumentException('The mode ' . $mode . ' is invalid.');
            }

            throw new \RuntimeException('The file ' . $filename . ' cannot be opened.');
        }

        return Stream::create($resource);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return Stream::create($resource);
    }

    public function createUploadedFile(StreamInterface $stream, int $size = null, int $error = \UPLOAD_ERR_OK, string $clientFilename = null, string $clientMediaType = null): UploadedFileInterface
    {
        if (null === $size) {
            $size = $stream->getSize();
        }

        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }


}