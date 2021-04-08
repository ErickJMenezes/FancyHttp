<?php


namespace ErickJMenezes\FancyHttp\Traits;


use InvalidArgumentException;

trait ExpectsArray
{
    /**
     * @param mixed $value
     */
    public function check(mixed $value): void
    {
        !is_array($value) && throw new InvalidArgumentException(sprintf(
            "The attribute %s was expecting an argument of type array, %s given.",
            static::class, gettype($value)
        ));
    }
}