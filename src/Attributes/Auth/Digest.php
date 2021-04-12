<?php


namespace FancyHttp\Attributes\Auth;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
class Digest extends Basic
{
}