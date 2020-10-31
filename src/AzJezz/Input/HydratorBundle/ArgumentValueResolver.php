<?php

declare(strict_types=1);

namespace AzJezz\Input\HydratorBundle;

use AzJezz\Input\Exception\BadInputException;
use AzJezz\Input\Hydrator;
use AzJezz\Input\HydratorInterface;
use AzJezz\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use function explode;
use function is_subclass_of;

final class ArgumentValueResolver implements ArgumentValueResolverInterface
{
    private HydratorInterface $hydrator;

    public function __construct(HydratorInterface $hydrator = null)
    {
        $this->hydrator = $hydrator ?? new Hydrator();
    }

    /**
     * Whether this resolver can resolve the value for the given ArgumentMetadata.
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($argument->isVariadic()) {
            return false;
        }

        $type = $argument->getType();
        if (null === $type) {
            return false;
        }

        return array_reduce(
            explode('|', $type),
            static fn(bool $carry, string $type): bool => $carry && is_subclass_of($type, InputInterface::class),
            true,
        );
    }

    /**
     * Returns the possible value(s).
     *
     * @throws BadRequestHttpException
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Request::METHOD_POST === $request->getMethod()) {
            $input = $request->request->all();
        } else {
            $input = $request->query->all();
        }

        /** @var non-empty-list<class-string<InputInterface>> $types */
        $types = explode('|', (string) $argument->getType());
        $exceptions = [];
        foreach ($types as $type) {
            try {
                yield $this->hydrator->hydrate($type, $input);
            } catch (BadInputException $exception) {
                $exceptions[] = $exception;
            }
        }

        $exception = $exceptions[0] ?? null;
        if (null !== $exception) {
            if ($argument->hasDefaultValue()) {
                /** @psalm-suppress MissingThrowsDocblock as the default value is present. */
                yield $argument->getDefaultValue();
            } elseif ($argument->isNullable()) {
                yield null;
            } else {
                throw new BadRequestHttpException($exception->getMessage(), $exception);
            }
        }
    }
}
