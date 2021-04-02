<?php


namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Delete extends AbstractHttpMethod
{
    public const METHOD = 'delete';
}