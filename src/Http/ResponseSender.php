<?php

namespace Dissonance\Http;




use Dissonance\Core\Support\RenderableInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseSender implements RenderableInterface
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
    }

    public function render()
    {
        $response = $this->response;

        $http_line = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        header($http_line, true, $response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        $stream = $response->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
    }

    public function __toString()
    {
        ob_start();
        $this->render();
        return ob_get_clean();
    }


}