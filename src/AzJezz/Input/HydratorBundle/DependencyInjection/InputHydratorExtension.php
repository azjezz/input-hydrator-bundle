<?php

declare(strict_types=1);

namespace AzJezz\Input\HydratorBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class InputHydratorExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @throws Exception If something went wrong.
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(
            __DIR__ . '/../Resources/config'
        ));

        $loader->load('services.php');
    }
}
