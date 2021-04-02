<?php


namespace ErickJMenezes\FancyHttp\Attributes;


#[\Attribute(\Attribute::TARGET_METHOD)]
class Post extends AbstractHttpMethod
{
    public const METHOD = 'post';
}