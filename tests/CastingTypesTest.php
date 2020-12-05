<?php

use PHPUnit\Framework\TestCase;
use ErickJMenezes\Http\Client;
use Psr\Http\Message\ResponseInterface;
use Tests\Clients\TodoListClient;


/**
 * Class CastingTypesTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class CastingTypesTest extends TestCase
{
    /**
     * @return \ErickJMenezes\Http\Client|mixed|\Tests\Clients\TodoListClient
     */
    public function testCreatingInstance()
    {
        $instance = Client::createFromInterface(TodoListClient::class);
        $this->assertTrue((bool)$instance, 'instance not created');
        return $instance;
    }

    /**
     * @param \Tests\Clients\TodoListClient $client
     * @depends testCreatingInstance
     */
    public function testArray($client)
    {
        $response = $client->getTodoByIdArray(1);
        $this->assertIsArray($response, 'Response is not array');
    }

    /**
     * @param \Tests\Clients\TodoListClient $client
     * @depends testCreatingInstance
     */
    public function testObject($client)
    {
        $response = $client->getTodoByIdObject(1);
        $this->assertIsObject($response, 'Response is not object');
    }

    /**
     * @param \Tests\Clients\TodoListClient $client
     * @depends testCreatingInstance
     */
    public function testResponseInterface($client)
    {
        $response = $client->getTodoByIdResponseInterface(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface');
    }

    /**
     * @param \Tests\Clients\TodoListClient $client
     * @depends testCreatingInstance
     */
    public function testVoid($client)
    {
        $response = $client->getTodoByIdVoid(1);
        $this->assertTrue(is_null($response), 'Response is not void');
    }

    /**
     * @param \Tests\Clients\TodoListClient $client
     * @depends testCreatingInstance
     */
    public function testString($client)
    {
        $response = $client->getTodoByIdString(1);
        $this->assertIsString($response, 'Response is not string');
    }

    /**
     * @param \Tests\Clients\TodoListClient $client
     * @depends testCreatingInstance
     */
    public function testBoolean($client)
    {
        $response = $client->getTodoByIdBoolean(1);
        $this->assertIsBool($response, 'Response is not boolean');
    }

    /**
     * @param \Tests\Clients\TodoListClient $client
     * @depends testCreatingInstance
     */
    public function testMixed($client)
    {
        $response = $client->getTodoByIdMixed(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface (mixed)');
    }

    /**
     * @param \Tests\Clients\TodoListClient $client
     * @depends testCreatingInstance
     */
    public function testNone($client)
    {
        $response = $client->getTodoByIdNone(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface (none)');
    }
}