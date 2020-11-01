<?php

declare(strict_types=1);

namespace AzJezz\Input\HydratorBundle\Test;

use AzJezz\Input\HydratorBundle\ArgumentValueResolver;
use AzJezz\Input\HydratorBundle\Test\Fixture\TokenCredentials;
use AzJezz\Input\HydratorBundle\Test\Fixture\UsernamePasswordCredentials;
use AzJezz\Input\HydratorBundle\Test\Fixture\Search;
use Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class ArgumentValueResolverTest extends TestCase
{
    private ArgumentValueResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new ArgumentValueResolver();
    }

    public function testThatItWorksAsExpected(): void
    {
        $request = Request::create('/search', Request::METHOD_GET, ['query' => 'hello'], [], [], [], []);
        $argument = new ArgumentMetadata('search', Search::class, false, false, null, false);

        static::assertTrue($this->resolver->supports($request, $argument));

        /** @var Generator<int, Search, void, mixed> $values */
        $values = $this->resolver->resolve($request, $argument);
        /** @var Search $search */
        $search = iterator_to_array($values)[0];

        static::assertSame('hello', $search->query);
    }

    public function testThatItDoesntSupportVariadicArguments(): void
    {
        $request = Request::create('/search', Request::METHOD_GET, ['query' => 'hello'], [], [], [], []);
        $argument = new ArgumentMetadata('search', Search::class, true, false, null, false);

        static::assertFalse($this->resolver->supports($request, $argument));
    }

    public function testThatItDoesntSupportNonInputObjects(): void
    {
        $request = Request::create('/search', Request::METHOD_GET, ['query' => 'hello'], [], [], [], []);
        $argument = new ArgumentMetadata('search', static::class, false, false, null, false);

        static::assertFalse($this->resolver->supports($request, $argument));
    }

    public function testThatItDoesntSupportUntypedArguments(): void
    {
        $request = Request::create('/search', Request::METHOD_GET, ['query' => 'hello'], [], [], [], []);
        $argument = new ArgumentMetadata('search', null, false, false, null, false);

        static::assertFalse($this->resolver->supports($request, $argument));
    }

    public function testThatItThrowsBadRequestHttpExceptionForBadInput(): void
    {
        $request = Request::create('/search', Request::METHOD_GET, ['query' => []], [], [], [], []);
        $argument = new ArgumentMetadata('search', Search::class, false, false, null, false);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('field "query" has an incorrect type of "array", "string" was expected.');

        /** @var Generator<int, Search, void, mixed> $values */
        $values = $this->resolver->resolve($request, $argument);
        iterator_to_array($values)[0];
    }

    public function testThatItWorksWithPostRequests(): void
    {
        $request = Request::create('/search', Request::METHOD_POST, ['query' => 'hello'], [], [], [], []);
        $argument = new ArgumentMetadata('search', Search::class, false, false, null, false);

        /** @var Generator<int, Search, void, mixed> $values */
        $values = $this->resolver->resolve($request, $argument);
        /** @var Search $search */
        $search = iterator_to_array($values)[0];

        static::assertSame('hello', $search->query);
    }

    public function testThatItDoesntHydrateAllDTOs(): void
    {
        $request = Request::create('/login', Request::METHOD_POST);
        $request->request->set('username', 'azjezz');
        $request->request->set('password', '123456789');
        $request->request->set('token', 'secret');

        $argument = new ArgumentMetadata('credentials', sprintf('%s|%s', UsernamePasswordCredentials::class, TokenCredentials::class), false, false, null, false);

        $values = $this->resolver->resolve($request, $argument);
        $values = iterator_to_array($values);

        self::assertCount(1, $values);
        self::assertInstanceOf(UsernamePasswordCredentials::class, $values[0]);
        self::assertSame('azjezz', $values[0]->username);
        self::assertSame('123456789', $values[0]->password);
    }

    public function testThatItDoesntThrowWhenOnOfTheDTOsIsInvalid(): void
    {
        $request = Request::create('/login', Request::METHOD_POST);
        $request->request->set('username', 'azjezz');
        $request->request->set('password', []);
        $request->request->set('token', 'secret');

        $argument = new ArgumentMetadata('credentials', sprintf('%s|%s', UsernamePasswordCredentials::class, TokenCredentials::class), false, false, null, false);

        $values = $this->resolver->resolve($request, $argument);
        $values = iterator_to_array($values);

        self::assertCount(1, $values);
        self::assertInstanceOf(TokenCredentials::class, $values[0]);
        self::assertSame('secret', $values[0]->token);
    }

    public function testThatItDoesntUseQueryParametersOnNonGetRequests(): void
    {
        $request = Request::create('/search', Request::METHOD_DELETE);
        $request->request->set('query', 'REQUEST');
        $request->query->set('query', 'QUERY');

        $argument = new ArgumentMetadata('search', Search::class, false, false, null, false);

        /** @var Generator<int, Search, void, mixed> $values */
        $values = $this->resolver->resolve($request, $argument);
        /** @var Search $search */
        $search = iterator_to_array($values)[0];

        static::assertSame('REQUEST', $search->query);
    }

    public function testThatItDoesntUseQueryParametersInPostRequests(): void
    {
        $request = Request::create('/search', Request::METHOD_POST);
        $request->query->add(['query' => 'hello']);

        $argument = new ArgumentMetadata('search', Search::class, false, false, null, false);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('required field "query" is missing from the request.');

        /** @var Generator<int, Search, void, mixed> $values */
        $values = $this->resolver->resolve($request, $argument);
        iterator_to_array($values)[0];
    }

    public function testThatItDoesntUseRequestParametersInGetRequests(): void
    {
        $request = Request::create('/search', Request::METHOD_GET);
        $request->request->add(['query' => 'hello']);

        $argument = new ArgumentMetadata('search', Search::class, false, false, null, false);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('required field "query" is missing from the request.');

        /** @var Generator<int, Search, void, mixed> $values */
        $values = $this->resolver->resolve($request, $argument);
        iterator_to_array($values)[0];
    }

    public function testThatItDoesntThrowForMissingOptionalArgument(): void
    {
        $request = Request::create('/search', Request::METHOD_GET);

        $argument = new ArgumentMetadata('search', Search::class, false, true, null, false);

        /** @var Generator<int, null|Search, void, mixed> $values */
        $values = $this->resolver->resolve($request, $argument);
        $search = iterator_to_array($values)[0];

        static::assertNull($search);
    }

    public function testThatItDoesntThrowForMissingNullableArgument(): void
    {
        $request = Request::create('/search', Request::METHOD_GET);

        $argument = new ArgumentMetadata('search', Search::class, false, false, null, true);

        /** @var Generator<int, null|Search, void, mixed> $values */
        $values = $this->resolver->resolve($request, $argument);
        $search = iterator_to_array($values)[0];

        static::assertNull($search);
    }

    public function testThatItDoesntThrowForMissingNullableAndOptionalArgument(): void
    {
        $request = Request::create('/search', Request::METHOD_GET);

        $argument = new ArgumentMetadata('search', Search::class, false, true, null, true);

        /** @var Generator<int, null|Search, void, mixed> $values */
        $values = $this->resolver->resolve($request, $argument);
        $search = iterator_to_array($values)[0];

        static::assertNull($search);
    }
}
