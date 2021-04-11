<?php

namespace Tests;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Tests\Clients\ClientSetup;
use Tests\Clients\FooInterface;


/**
 * Class CastingTypesTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class CastingTypesTest extends TestCase
{
    use ClientSetup;

    protected function prepareHandler(int $times = 1): void
    {
        for ($i = 0; $i < $times; $i++)
            $this->handler->append(new Response(
                headers: ['Content-Type' => 'application/json'],
                body: json_encode(['foo' => 'bar'])
            ));
    }

    public function testArray()
    {
        $this->prepareHandler();
        $response = $this->client->castToArray();
        $this->assertIsArray($response);
        self::assertTrue($response['foo'] === 'bar');
    }

    public function testObject()
    {
        $this->prepareHandler();
        $response = $this->client->castToObject();
        self::assertIsObject($response);
        self::assertTrue($response->foo === 'bar');
    }

    public function testBoolean()
    {
        $this->prepareHandler();
        $response = $this->client->castToBool();
        self::assertTrue($response);
    }

    public function testString()
    {
        $this->prepareHandler();
        $response = $this->client->castToString();
        self::assertIsString($response);
        self::assertTrue($response === '{"foo":"bar"}');
    }

    public function testInt()
    {
        $this->prepareHandler();
        $response = $this->client->castToInt();
        self::assertIsInt($response);
        self::assertTrue($response === 200);
    }

    public function testArrayObject()
    {
        $this->prepareHandler();
        $response = $this->client->castToArrayObject();
        self::assertTrue($response->foo === 'bar');
    }

    public function testResponseInterface()
    {
        $this->prepareHandler();
        $response = $this->client->castToResponse();
        $this->assertTrue($response->getBody()->getContents() === '{"foo":"bar"}');
    }

    public function testVoid()
    {
        $this->prepareHandler();
        $response = $this->client->castToVoid();
        $this->assertTrue(is_null($response), 'Response is not void');
    }

    public function testMixed()
    {
        $this->prepareHandler();
        $response = $this->client->castToMixed();
        $this->assertTrue($response instanceof ResponseInterface);
        $this->assertTrue($response->getBody()->getContents() === '{"foo":"bar"}');
    }

    public function testDefault()
    {
        $this->prepareHandler();
        $response = $this->client->castToDefault();
        $this->assertTrue($response instanceof ResponseInterface);
        $this->assertTrue($response->getBody()->getContents() === '{"foo":"bar"}');
    }

    public function testCastable()
    {
        $this->prepareHandler();
        $response = $this->client->castToCastable();
        self::assertTrue($response->foo === 'bar');
    }

    public function testAutoMapped()
    {
        $this->prepareHandler();
        $fooInterface = $this->client->castToAutoMapped();
        $this->checkFooInterface($fooInterface);
    }

    public function testAutoMappedList()
    {
        $this->handler->append(new Response(
            headers: ['Content-Type' => 'application/json'],
            body: json_encode([['foo' => 'bar'], ['foo' => 'bar']])
        ));
        $fooInterfaceList = $this->client->castToAutoMappedList();
        foreach ($fooInterfaceList as $fooInterface) {
            self::assertTrue($fooInterface instanceof FooInterface, 'Something goes wrong');
            $this->checkFooInterface($fooInterface);
        }
    }

    /**
     * @param \Tests\Clients\FooInterface $fooInterface
     */
    private function checkFooInterface(FooInterface $fooInterface): void
    {
        self::assertTrue($fooInterface->getFoo() === 'bar');
        self::assertTrue($fooInterface->foo === 'bar');
        self::assertTrue($fooInterface['foo'] === 'bar');
        self::assertTrue((string)$fooInterface === '{"foo":"bar"}');
        self::assertTrue($fooInterface->jsonSerialize() === ['foo' => 'bar']);
        foreach ($fooInterface as $key => $value)
            self::assertTrue($key === 'foo' && $value === 'bar');
    }
}