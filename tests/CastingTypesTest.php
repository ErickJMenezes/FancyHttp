<?php

namespace Tests;

use ErickJMenezes\FancyHttp\Client;
use PHPUnit\Framework\TestCase;
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
    public function testCreatingInstance(): TestCaseClient
    {
        $instance = Client::createFromInterface(TestCaseClient::class, 'https://jsonplaceholder.typicode.com/');
        $this->assertTrue($instance instanceof TestCaseClient, 'instance not created');
        return $instance;
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testArray(TestCaseClient $client)
    {
        $response = $client->getTodoByIdArray(1);
        $this->assertIsArray($response, 'Response is not array');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testObject(TestCaseClient $client)
    {
        $response = $client->getTodoByIdObject(1);
        $this->assertIsObject($response, 'Response is not object');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testResponseInterface(TestCaseClient $client)
    {
        $response = $client->getTodoByIdResponseInterface(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testVoid(TestCaseClient $client)
    {
        $response = $client->getTodoByIdVoid(1);
        $this->assertTrue(is_null($response), 'Response is not void');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testString(TestCaseClient $client)
    {
        $response = $client->getTodoByIdString(1);
        $this->assertIsString($response, 'Response is not string');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testBoolean(TestCaseClient $client)
    {
        $response = $client->getTodoByIdBoolean(1);
        $this->assertIsBool($response, 'Response is not boolean');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testMixed(TestCaseClient $client)
    {
        $response = $client->getTodoByIdMixed(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface (mixed)');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testNone(TestCaseClient $client)
    {
        $response = $client->getTodoByIdNone(1);
        $this->assertTrue($response instanceof ResponseInterface, 'Response is not ResponseInterface (none)');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testCastable(TestCaseClient $client)
    {
        $response = $client->getTodoByIdCastable(1);
        self::assertTrue(true);
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testModelInterface(TestCaseClient $client)
    {
        $todo = $client->getTodoByIdMapped(1);
        $todo->setTitle('testing-auto-mapped');
        self::assertTrue($todo->getTitle() === 'testing-auto-mapped', 'Something goes wrong');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testGetTodosMapped(TestCaseClient $client)
    {
        $todos = $client->getTodosMapped();
        foreach ($todos as $todo)
            self::assertTrue($todo instanceof \Tests\Clients\TodoInterface, 'Something goes wrong');
    }
}