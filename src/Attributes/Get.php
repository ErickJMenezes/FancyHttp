<?php

namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Get extends AbstractHttpMethod
{
    public const METHOD = 'get';
}