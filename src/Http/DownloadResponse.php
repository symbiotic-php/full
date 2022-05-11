<?php

namespace Symbiotic\Http;

use Psr\Http\Message\StreamInterface;

/**
 * Class Response
 */
class DownloadResponse extends Response
{

    public function __construct(StreamInterface $body, string $filename, int $status = 200, array $headers = [], string $version = '1.1', string $reason = null)
    {
        parent::__construct($status,  array_merge($headers, [
            'Content-Description' => 'File Transfer',
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . basename($filename).'"',
            'Content-Transfer-Encoding' => 'binary',
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => $body->getSize(),
        ]), $body, $version, $reason);
    }
}