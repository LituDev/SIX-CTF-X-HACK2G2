<?php

namespace App;

use App\Websocket\Authentication\Factory;
use Gos\Bundle\WebSocketBundle\DependencyInjection\GosWebSocketExtension;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var GosWebSocketExtension $extension */
        $extension = $container->getExtension('gos_web_socket');
        $extension->addAuthenticationProviderFactory(new Factory());
    }
}
