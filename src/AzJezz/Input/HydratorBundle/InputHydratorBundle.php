<?php

declare(strict_types=1);

namespace AzJezz\Input\HydratorBundle;

use AzJezz\Input\HydratorBundle\DependencyInjection\InputHydratorExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class InputHydratorBundle extends Bundle
{
    public function getContainerExtension(): InputHydratorExtension
    {
        return new InputHydratorExtension();
    }
}
