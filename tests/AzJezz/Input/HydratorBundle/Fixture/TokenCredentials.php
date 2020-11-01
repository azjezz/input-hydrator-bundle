<?php

declare(strict_types=1);

namespace AzJezz\Input\HydratorBundle\Test\Fixture;

use AzJezz\Input\InputInterface;

final class TokenCredentials implements InputInterface
{
    public string $token;
}
