<?php


namespace ErickJMenezes\FancyHttp\Attributes;

use Attribute;
use ErickJMenezes\FancyHttp\Contracts\ParameterAttribute;
use ErickJMenezes\FancyHttp\Traits\ExpectsArray;

/**
 * Class QueryParams
 *
 * An key-value array to be used as query strings.
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp\Attributes
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class QueryParams implements ParameterAttribute
{
    use ExpectsArray;
}