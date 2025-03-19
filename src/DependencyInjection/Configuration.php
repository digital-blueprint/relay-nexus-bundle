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
            ->addPolicy(self::ROLE_USER, 'false', 'Returns true if the user is allowed to use the nexus API.')
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
                        ->scalarNode('host')
                            ->info('host name of the Typesense server')
                            ->isRequired()
                        ->end()
                        ->scalarNode('prot')
                            ->info('Protocol http or https')
                            ->defaultValue('http')
                        ->end()
                        ->scalarNode('port')
                            ->info('Port (e.g. 8108)')
                            ->defaultValue(8108)
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
                        ->scalarNode('api_key')
                            ->info('API key for the Typesense proxy connection')
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
