<?php

declare(strict_types=1);

use AzJezz\Input\Hydrator;
use AzJezz\Input\HydratorBundle\ArgumentValueResolver;
use AzJezz\Input\HydratorInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $configurator, ContainerBuilder $container): void {
    $configurator->services()
        ->set(Hydrator::class, Hydrator::class)

        ->alias(HydratorInterface::class, Hydrator::class)

        ->set(ArgumentValueResolver::class)
            ->args([
                new Reference(HydratorInterface::class)
            ])
            ->tag('controller.argument_value_resolver', [
                'priority' => 50
            ]);
};
