<?php


namespace FancyHttp\Attributes;


use Attribute;

/**
 * Attribute AutoMapped
 *
 * Use this attribute to tell the client that your interface must be considered as a
 * valid return type and he must generate an implementation for it.
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package FancyHttp\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
class AutoMapped
{
}