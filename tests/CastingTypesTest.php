<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tests\Clients\ClientSetup;


/**
 * Class CastingTypesTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class CastingTypesTest extends TestCase
{
    use ClientSetup;

    public function testArray()
    {
        $response = $this->client->getTodoByIdArray(1);
        $this->assertIsArray($response, 'Response is not array');
    }

    public function testObject()
    {
        $response = $this->client->getTodoByIdObject(1);
        $this->assertIsObject($response, 'Response is not object');
    }

    public function testResponseInterface()
    {
        $response = $this->client->getTodoByIdResponseInterface(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface');
    }

    public function testVoid()
    {
        $response = $this->client->getTodoByIdVoid(1);
        $this->assertTrue(is_null($response), 'Response is not void');
    }

    public function testString()
    {
        $response = $this->client->getTodoByIdString(1);
        $this->assertIsString($response, 'Response is not string');
    }

    public function testBoolean()
    {
        $response = $this->client->getTodoByIdBoolean(1);
        $this->assertIsBool($response, 'Response is not boolean');
    }

    public function testMixed()
    {
        $response = $this->client->getTodoByIdMixed(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface (mixed)');
    }

    public function testNone()
    {
        $response = $this->client->getTodoByIdNone(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface (none)');
    }

    public function testCastable()
    {
        $response = $this->client->getTodoByIdCastable(1);
        self::assertTrue(true);
    }

    public function testModelInterface()
    {
        $todo = $this->client->getTodoByIdMapped(1);
        $todo->setTitle('testing-auto-mapped');
        self::assertTrue($todo->getTitle() === 'testing-auto-mapped', 'Something goes wrong');
    }

    public function testGetTodosMapped()
    {
        $todos = $this->client->getTodosMapped();
        foreach ($todos as $todo)
            self::assertTrue($todo instanceof \Tests\Clients\TodoInterface, 'Something goes wrong');
    }
}