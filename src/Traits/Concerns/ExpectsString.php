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
trait ExpectsString
{
    public function check(mixed $value): void
    {
        !is_string($value) && !is_a($value, Stringable::class)
        && throw new InvalidArgumentException(sprintf(
            "The attribute %s was expecting string, %s given.",
            static::class, gettype($value)
        ));
    }
}