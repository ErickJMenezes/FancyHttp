<?php

use PHPUnit\Framework\TestCase;
use ErickJMenezes\FancyHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Tests\Clients\TestCaseClient;


/**
 * Class CastingTypesTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class CastingTypesTest extends TestCase
{
    /**
     * @return Client
     */
    public function testCreatingInstance(): Client
    {
        $instance = Client::createFromInterface(TestCaseClient::class,  'https://jsonplaceholder.typicode.com/');
        $this->assertTrue((bool)$instance, 'instance not created');
        return $instance;
    }

    /**
     * @param Client $client
     * @depends testCreatingInstance
     */
    public function testArray(Client $client)
    {
        $response = $client->getTodoByIdArray(1);
        $this->assertIsArray($response, 'Response is not array');
    }

    /**
     * @param Client $client
     * @depends testCreatingInstance
     */
    public function testObject(Client $client)
    {
        $response = $client->getTodoByIdObject(1);
        $this->assertIsObject($response, 'Response is not object');
    }

    /**
     * @param Client $client
     * @depends testCreatingInstance
     */
    public function testResponseInterface(Client $client)
    {
        $response = $client->getTodoByIdResponseInterface(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface');
    }

    /**
     * @param Client $client
     * @depends testCreatingInstance
     */
    public function testVoid(Client $client)
    {
        $response = $client->getTodoByIdVoid(1);
        $this->assertTrue(is_null($response), 'Response is not void');
    }

    /**
     * @param Client $client
     * @depends testCreatingInstance
     */
    public function testString(Client $client)
    {
        $response = $client->getTodoByIdString(1);
        $this->assertIsString($response, 'Response is not string');
    }

    /**
     * @param Client $client
     * @depends testCreatingInstance
     */
    public function testBoolean(Client $client)
    {
        $response = $client->getTodoByIdBoolean(1);
        $this->assertIsBool($response, 'Response is not boolean');
    }

    /**
     * @param Client $client
     * @depends testCreatingInstance
     */
    public function testMixed(Client $client)
    {
        $response = $client->getTodoByIdMixed(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface (mixed)');
    }

    /**
     * @param Client $client
     * @depends testCreatingInstance
     */
    public function testNone(Client $client)
    {
        $response = $client->getTodoByIdNone(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface (none)');
    }
}