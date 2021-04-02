<?php


namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Patch extends AbstractHttpMethod
{
    public const METHOD = 'patch';
}