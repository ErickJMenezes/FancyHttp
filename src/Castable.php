<?php


namespace FancyHttp;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface Castable
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package FancyHttp
 */
interface Castable
{
    public static function castResponse(ResponseInterface $response): Castable;
}