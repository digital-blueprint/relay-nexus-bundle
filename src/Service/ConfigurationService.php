<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Service;

class ConfigurationService
{
    private array $config = [];

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getTypesenseHost(): string
    {
        return $this->config['typesense']['host'];
    }

    public function getTypesenseProt(): string
    {
        return $this->config['typesense']['prot'];
    }

    public function getTypesensePort(): int
    {
        return (int)($this->config['typesense']['port']);
    }

    public function getTypesenseApiKey(): string
    {
        return $this->config['typesense']['api_key'];
    }

    /**
     * Returns the typesense API key used when talking to typesense via the proxy. It is read-only, and is limited
     * to a specific collection and actions on that collection.
     */
    public function getTypesenseProxyApiKey(): string
    {
        return $this->config['frontend']['api_key'];
    }

    public function getTopics(): array
    {
        return $this->config['topics'] ?? [];
    }

    public function getAliasName(): string
    {
        return $this->config['frontend']['alias'] ?? '';
    }
}
