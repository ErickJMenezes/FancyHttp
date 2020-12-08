<?php


use \Tests\Clients\InterfaceWithoutApiAttribute;
use ErickJMenezes\FancyHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Class InterfaceTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class InterfaceTest extends TestCase
{
    public function testTryToCreateAClientWithoutApiAttribute()
    {
        $this->expectException(InvalidArgumentException::class);
        Client::createFromInterface(InterfaceWithoutApiAttribute::class,  'https://jsonplaceholder.typicode.com/');
    }
}