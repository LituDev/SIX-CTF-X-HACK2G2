<?php

namespace App\Websocket\Authentication;

use Gos\Bundle\WebSocketBundle\DependencyInjection\Factory\Authentication\AuthenticationProviderFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class Factory implements AuthenticationProviderFactoryInterface
{

    public function createAuthenticationProvider(ContainerBuilder $container, array $config): string
    {
        $providerId = "app.auth";

        $container->setDefinition($providerId, new Definition(Provider::class))
            ->setAutowired(true);

        return $providerId;
    }

    public function getKey(): string
    {
        return 'app';
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
    }
}
