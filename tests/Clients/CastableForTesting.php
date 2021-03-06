<?php


namespace Tests\Clients;

use FancyHttp\Castable;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CastableForTesting
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests\Clients
 */
class CastableForTesting implements Castable
{
    public function __construct(
        public string $foo
    )
    {
    }

    public static function castResponse(ResponseInterface $response): Castable
    {
        $data = json_decode($response->getBody()->getContents());
        return new self($data->foo);
    }
}