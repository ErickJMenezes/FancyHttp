<?php


namespace ErickJMenezes\FancyHttp\Attributes\Auth;

use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Digest implements ParameterAttribute
{
    public function check(mixed $value): void
    {
        (!is_array($value) || count($value) !== 3) &&
        throw new \InvalidArgumentException(
            "The value of basic authorization must be an array with 3 values: username, password and digest."
        );
    }
}