<?php

namespace Symbiotic\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;


class ResponseMutable implements ResponseInterface
{
    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return ResponseInterface
     */
    public function getRealInstance()
    {
        return $this->response;
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion()
    {
        return $this->response->getProtocolVersion();
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
        $this->response = $this->response->withProtocolVersion($version);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders()
    {
        return $this->response->getHeaders();
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name)
    {
        return $this->response->hasHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        return $this->response->getHeader($name);
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name)
    {
        return $this->response->getHeaderLine($name);
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
        $this->response = $this->response->withHeader($name, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        $this->response = $this->response->withAddedHeader($name, $value);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        $this->response = $this->response->withoutHeader($name);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBody()
    {
        return $this->response->getBody();
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        $this->response = $this->response->withBody($body);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStatusCode()
    {
        return $this->response->getStatusCode();
    }

    /**
     * @inheritDoc
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $this->response = $this->response->withStatus($code, $reasonPhrase);

        return $this;

    }

    /**
     * @inheritDoc
     */
    public function getReasonPhrase()
    {
        return $this->response->getReasonPhrase();
    }

}


