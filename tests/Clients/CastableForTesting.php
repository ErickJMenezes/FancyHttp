<?php


namespace Tests\Clients;

use ErickJMenezes\FancyHttp\Castable;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CastableForTesting
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests\Clients
 */
class CastableForTesting implements Castable
{
    public static function castResponse(ResponseInterface $response): static
    {
        return new static();
    }
}