<?php

declare(strict_types=1);

namespace AzJezz\Input\HydratorBundle\Test\Fixture;

use AzJezz\Input\InputInterface;

final class UsernamePasswordCredentials implements InputInterface
{
    public string $username;
    public string $password;
}
