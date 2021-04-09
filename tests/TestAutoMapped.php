<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Clients\ClientSetup;

/**
 * Class VerbsAndSomeParametersTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class TestAutoMapped extends TestCase
{
    use ClientSetup;

    public function testAccessors()
    {
        $todo = $this->client->getTodoByIdMapped(1);
        self::assertTrue(is_int($todo->id));
    }

    public function testOffsets()
    {
        $todo = $this->client->getTodoByIdMapped(1);
        self::assertTrue(is_int($todo['id']));
    }

    public function testImplementingJsonSerializable()
    {
        $todo = $this->client->getTodoByIdMapped(1);
        $serialized = $todo->jsonSerialize();
        self::assertIsArray($serialized);
    }
}
