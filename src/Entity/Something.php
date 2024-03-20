<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

class Something
{
    private $identifier;

    /**
     * @Groups({"NexusSomething:output", "NexusSomething:input"})
     *
     * @var string
     */
    private $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }
}
