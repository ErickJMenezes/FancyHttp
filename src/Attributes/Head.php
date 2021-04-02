<?php

namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Head extends AbstractHttpMethod
{
    public const METHOD = 'head';
}