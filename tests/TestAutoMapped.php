<?php declare(strict_types=1);

namespace Tests;

use ErickJMenezes\FancyHttp\Client;
use PHPUnit\Framework\TestCase;
use Tests\Clients\TestCaseClient;

/**
 * Class VerbsAndSomeParametersTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class TestAutoMapped extends TestCase
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
    public function testAccessors(TestCaseClient $client)
    {
        $todo = $client->getTodoByIdMapped(1);
        self::assertTrue(is_int($todo->id));
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testOffsets(TestCaseClient $client)
    {
        $todo = $client->getTodoByIdMapped(1);
        self::assertTrue(is_int($todo['id']));
    }

    /**
     * @depends testCreatingInstance
     * @param \Tests\Clients\TestCaseClient $client
     */
    public function testImplementingJsonSerializable(TestCaseClient $client)
    {
        $todo = $client->getTodoByIdMapped(1);
        $serialized = $todo->jsonSerialize();
        self::assertIsArray($serialized);
    }
}
