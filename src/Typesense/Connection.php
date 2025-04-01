<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Typesense;

use Symfony\Component\HttpClient\HttplugClient;
use Typesense\Client;

class Connection
{
    protected $client;

    public function __construct($apiUrl, $apikey)
    {
        $parsedUrl = parse_url($apiUrl);
        if ($parsedUrl === false) {
            throw new \InvalidArgumentException('Invalid url provided');
        }
        $scheme = $parsedUrl['scheme'] ?? 'http';
        $host = $parsedUrl['host'] ?? 'localhost';
        $port = $parsedUrl['port'] ?? ($scheme === 'https' ? 443 : ($scheme === 'http' ? 80 : '8108'));

        $this->client = new Client(
            [
                'api_key' => $apikey,
                'nodes' => [
                    [
                        'host' => $host,
                        'port' => $port,
                        'protocol' => $scheme,
                    ],
                ],
                'client' => new HttplugClient(),
            ]
        );
    }

    public function getClient()
    {
        return $this->client;
    }
}
