<?php

namespace Dbp\Relay\NexusBundle\Typesense;

use Symfony\Component\HttpClient\HttplugClient;
use Typesense\Client;

class Connection
{
    protected $client;

    public function __construct($apikey, $host = 'localhost', $port = '8108', $protocol = 'http')
    {
        $this->client = new Client(
            [
                'api_key' => $apikey,
                'nodes' => [
                    [
                        'host' => $host,
                        'port' => $port,
                        'protocol' => $protocol,
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