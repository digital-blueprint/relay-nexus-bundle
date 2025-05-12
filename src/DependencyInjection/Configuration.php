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
                    ->info('strings are URLs to the `topic.metatdada.json` files of the apps')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('typesense')
                    ->info('Settings for the internal connection to the typesense server')
                    ->children()
                        ->scalarNode('api_url')
                            ->info('Typesense API URL of the internal typesense server')
                            ->isRequired()
                        ->end()
                        ->scalarNode('api_key')
                            ->info('Typesense API key to create, query and delete typesense collections')
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
