<?php

declare(strict_types=1);

namespace AzJezz\Input\HydratorBundle\Test\Fixture;

use AzJezz\Input\InputInterface;

/**
 * @psalm-immutable
 */
final class Search implements InputInterface
{
    public string $query;
    public ?int   $limit = 100;
}
