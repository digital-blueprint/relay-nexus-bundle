<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\DependencyInjection;

use Dbp\Relay\CoreBundle\Authorization\AuthorizationConfigDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /** Roles */
    public const ROLE_USER = 'ROLE_USER';

    private function getAuthNode(): NodeDefinition
    {
        return AuthorizationConfigDefinition::create()
            ->addRole(self::ROLE_USER, 'false', 'Returns true if the user is allowed to use the nexus API.')
            ->getNodeDefinition();
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('dbp_relay_nexus');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('topics')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('typesense')
                    ->children()
                        ->scalarNode('api_url')
                            ->info('URL for the Typesense server')
                            ->isRequired()
                        ->end()
                        ->scalarNode('api_key')
                            ->info('API key for the Typesense server')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('frontend')
                    ->children()
                        ->scalarNode('alias')
                            ->info('Alias of the current Typesense collection')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->append($this->getAuthNode())
            ->end();

        return $treeBuilder;
    }
}
