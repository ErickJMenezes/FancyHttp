<?php


namespace FancyHttp\Attributes;

use Attribute;
use FancyHttp\Contracts\ParameterAttribute;
use FancyHttp\Traits\Concerns\ExpectsArray;

/**
 * Class QueryParams
 *
 * An key-value array to be used as query strings.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package FancyHttp\Attributes
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Query implements ParameterAttribute
{
    use ExpectsArray;
}