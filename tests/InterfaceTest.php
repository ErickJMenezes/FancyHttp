<?php


use Clients\InterfaceWithoutApiAttribute;
use ErickJMenezes\Http\Client;
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
        $client = Client::createFromInterface(InterfaceWithoutApiAttribute::class);
    }
}