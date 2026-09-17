<?php

declare(strict_types=1);

namespace SarissaOps\Enviatodo;

use SarissaOps\Enviatodo\Exception\InvalidArgumentException;

/**
 * Proxy for Webmozart\Assert: callers get our own exception type, so the SDK
 * exposes exactly one exception hierarchy rooted at Exception\Exception.
 *
 * Explicitly lists methods (no @mixin) to stay compatible with both
 * webmozart/assert 1.x and 2.x.
 *
 * @method static void stringNotEmpty(mixed $value, string $message = '')
 * @method static void string(mixed $value, string $message = '')
 * @method static void notEmpty(mixed $value, string $message = '')
 * @method static void isArray(mixed $value, string $message = '')
 * @method static void isList(mixed $value, string $message = '')
 * @method static void allString(mixed $value, string $message = '')
 * @method static void boolean(mixed $value, string $message = '')
 * @method static void integer(mixed $value, string $message = '')
 * @method static void numeric(mixed $value, string $message = '')
 * @method static void greaterThan(mixed $value, mixed $limit, string $message = '')
 * @method static void greaterThanEq(mixed $value, mixed $limit, string $message = '')
 * @method static void range(mixed $value, mixed $min, mixed $max, string $message = '')
 * @method static void oneOf(mixed $value, array<mixed> $values, string $message = '')
 * @method static void inArray(mixed $value, array<mixed> $values, string $message = '')
 * @method static void keyExists(array<mixed> $array, mixed $key, string $message = '')
 * @method static void nullOrString(mixed $value, string $message = '')
 * @method static void nullOrIsArray(mixed $value, string $message = '')
 */
final class Assert
{
    /**
     * @param array<int, mixed> $arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments)
    {
        try {
            return \Webmozart\Assert\Assert::$name(...$arguments);
            // @phpstan-ignore catch.neverThrown (dynamic dispatch hides the throw from analysis)
        } catch (\InvalidArgumentException $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
