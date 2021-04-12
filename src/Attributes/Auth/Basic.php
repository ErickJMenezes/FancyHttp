<?php


namespace FancyHttp\Attributes\Auth;

use Attribute;
use FancyHttp\Contracts\ParameterAttribute;
use InvalidArgumentException;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Basic implements ParameterAttribute
{
    public function check(mixed $value): void
    {
        (!is_array($value) || count($value) !== 2) &&
        throw new InvalidArgumentException(
            "The value of authorization must be an array with 2 values: username and password."
        );
    }
}