<?php


namespace ErickJMenezes\FancyHttp\Traits;


use InvalidArgumentException;
use Stringable;

trait ExpectsString
{
    public function check(mixed $value): void
    {
        (!is_string($value) && !is_a($value, Stringable::class) && !is_int($value))
        && throw new InvalidArgumentException(sprintf(
            "The attribute %s was expecting string or int, %s given.",
            static::class, gettype($value)
        ));
    }
}