<?php

declare(strict_types=1);

namespace Dbp\Relay\NexusBundle\DependencyInjection;

use Dbp\Relay\CoreBundle\Extension\ExtensionTrait;
use Dbp\Relay\NexusBundle\Authorization\AuthorizationService;
use Dbp\Relay\NexusBundle\Service\ConfigurationService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DbpRelayNexusExtension extends ConfigurableExtension
{
    use ExtensionTrait;

    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yaml');

        $this->addRouteResource($container, __DIR__.'/../Resources/config/routes.yaml', 'yaml');

        $definition = $container->getDefinition(ConfigurationService::class);
        $definition->addMethodCall('setConfig', [$mergedConfig]);

        $definition = $container->getDefinition(AuthorizationService::class);
        $definition->addMethodCall('setConfig', [$mergedConfig]);
    }
}
