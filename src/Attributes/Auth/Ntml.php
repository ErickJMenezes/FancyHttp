<?php


namespace ErickJMenezes\FancyHttp\Attributes\Auth;

use Attribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Ntml extends Digest
{
    public function check(mixed $value): void
    {
        (!is_array($value) || count($value) !== 3) &&
        throw new InvalidArgumentException(
            "The value of basic authorization must be an array with 3 values: username, password and ntml."
        );
    }
}