<?php


namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Put extends AbstractHttpMethod
{
    public const METHOD = 'put';
}