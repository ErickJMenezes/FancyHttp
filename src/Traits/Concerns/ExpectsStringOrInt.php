<?php


namespace FancyHttp\Traits\Concerns;


use InvalidArgumentException;
use Stringable;

/**
 * Trait ExpectsString
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package FancyHttp\Traits
 * @internal
 */
trait ExpectsStringOrInt
{
    public function check(mixed $value): void
    {
        !is_string($value) && !is_a($value, Stringable::class) && !is_int($value)
        && throw new InvalidArgumentException(sprintf(
            "The attribute %s was expecting string or int, %s given.",
            static::class, gettype($value)
        ));
    }
}