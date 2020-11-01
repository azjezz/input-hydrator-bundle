## Input Hydrator Bundle

![Unit tests status](https://github.com/azjezz/input-hydrator-bundle/workflows/unit%20tests/badge.svg?branch=develop)
![Static analysis status](https://github.com/azjezz/input-hydrator-bundle/workflows/static%20analysis/badge.svg?branch=develop)
![Security analysis status](https://github.com/azjezz/input-hydrator-bundle/workflows/security%20analysis/badge.svg?branch=develop)
![Coding standards status](https://github.com/azjezz/input-hydrator-bundle/workflows/coding%20standards/badge.svg?branch=develop)
[![TravisCI Build Status](https://travis-ci.com/azjezz/input-hydrator-bundle.svg?branch=develop)](https://travis-ci.com/azjezz/input-hydrator-bundle)
[![Coverage Status](https://coveralls.io/repos/github/azjezz/input-hydrator-bundle/badge.svg?branch=develop)](https://coveralls.io/github/azjezz/input-hydrator-bundle?branch=develop)
[![Type Coverage](https://shepherd.dev/github/azjezz/input-hydrator-bundle/coverage.svg)](https://shepherd.dev/github/azjezz/input-hydrator-bundle)
[![Total Downloads](https://poser.pugx.org/azjezz/input-hydrator-bundle/d/total.svg)](https://packagist.org/packages/azjezz/input-hydrator-bundle)
[![Latest Stable Version](https://poser.pugx.org/azjezz/input-hydrator-bundle/v/stable.svg)](https://packagist.org/packages/azjezz/input-hydrator-bundle)
[![License](https://poser.pugx.org/azjezz/input-hydrator-bundle/license.svg)](https://packagist.org/packages/azjezz/input-hydrator-bundle)

### About

InputHydratorBundle provides a Symfony Bundle for [`azjezz/input-hydrator`](https://github.com/azjezz/input-hydrator) package.

### Installation

To install the bundle, run the command below and you will get the latest version:

```console
$ composer require azjezz/input-hydrator-bundle
```

### Configuration

Configuring the input hydrator bundle is pretty straight forward,
all you need to do is add the bundle to your `config/bundles.php`:

> Note: this will be done for you automatically if you have `symfony/flex` installed.

```php
// config/bundles.php
<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    ...
    AzJezz\Input\HydratorBundle\InputHydratorBundle::class => ['all' => true],
];
```

That's it.

### Usage

To use the input hydrator, you first need to create your input DTO class.

for example:

```php
// src/Input/Search.php
<?php

declare(strict_types=1);

namespace App\Input;

use AzJezz\Input\InputInterface;

final class Search implements InputInterface
{
    public string $query;
}
```

later you can request the DTO as a parameter in your controller:

```
// src/Controller/SearchController.php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Input\Search;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SearchController
{
    /**
     * @Route("/search", methods={"GET"})
     */
    public function index(Search $search): Response
    {
        return new Response('You were looking for "'.$search->query.'"?');
    }
}
```

Using Symfony's argument resolver, the bundle is able to hydrate the `Search` DTO and pass it on to your controller.

In case the request doesn't specify the `query` field, or `query` contains another type ( e.g. `array` ),
the argument resolver will throw `BadRequestHttpException` which will result in a `400 Bad Request` response.

## License

The MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. 