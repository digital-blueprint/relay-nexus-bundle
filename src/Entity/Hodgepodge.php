<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use Dbp\Relay\NexusBundle\Rest\HodgepodgeProcessor;
use Dbp\Relay\NexusBundle\Rest\HodgepodgeProvider;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    shortName: 'NexusHodgepodge',
    types: ['https://schema.org/Hodgepodge'],
    operations: [
        new Get(
            uriTemplate: '/nexus/hodgepodges/{identifier}',
            openapi: new Operation(
                tags: ['Nexus']
            ),
            provider: HodgepodgeProvider::class
        ),
        new GetCollection(
            uriTemplate: '/nexus/hodgepodges',
            openapi: new Operation(
                tags: ['Nexus']
            ),
            provider: HodgepodgeProvider::class
        ),
        new Post(
            uriTemplate: '/nexus/hodgepodges',
            openapi: new Operation(
                tags: ['Nexus'],
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/ld+json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'name' => ['type' => 'string'],
                                ],
                                'required' => ['name'],
                            ],
                            'example' => [
                                'name' => 'Example Name',
                            ],
                        ],
                    ])
                )
            ),
            processor: HodgepodgeProcessor::class
        ),
        new Delete(
            uriTemplate: '/nexus/hodgepodges/{identifier}',
            openapi: new Operation(
                tags: ['Nexus']
            ),
            provider: HodgepodgeProvider::class,
            processor: HodgepodgeProcessor::class
        ),
    ],
    normalizationContext: ['groups' => ['NexusHodgepodge:output']],
    denormalizationContext: ['groups' => ['NexusHodgepodge:input']]
)]
class Hodgepodge
{
    #[ApiProperty(identifier: true)]
    #[Groups(['NexusHodgepodge:output'])]
    private ?string $identifier = null;

    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[Groups(['NexusHodgepodge:output', 'NexusHodgepodge:input'])]
    private ?string $name;

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(?string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
