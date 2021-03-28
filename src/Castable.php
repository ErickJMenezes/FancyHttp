<?php


namespace ErickJMenezes\FancyHttp;

use Psr\Http\Message\ResponseInterface;

/**
 * Interface Castable
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package ErickJMenezes\FancyHttp
 */
interface Castable
{
    public static function castResponse(ResponseInterface $response): mixed;
}