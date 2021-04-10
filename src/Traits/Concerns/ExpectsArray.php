<?php


namespace ErickJMenezes\FancyHttp\Traits\Concerns;


use InvalidArgumentException;

/**
 * Trait ExpectsArray
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Traits
 * @internal
 */
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