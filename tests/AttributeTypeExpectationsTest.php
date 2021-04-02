<?php


namespace Tests;


use ErickJMenezes\FancyHttp\Client;
use PHPUnit\Framework\TestCase;
use Tests\Clients\TestCaseClient;

/**
 * Class AttributeTypeExpectationsTest
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests
 * @covers
 */
class AttributeTypeExpectationsTest extends TestCase
{
    public function testCreatingInstance(): TestCaseClient
    {
        $instance = Client::createFromInterface(TestCaseClient::class, 'https://jsonplaceholder.typicode.com/');
        $this->assertTrue($instance instanceof TestCaseClient, 'instance not created');
        return $instance;
    }

    /**
     * @depends testCreatingInstance
     * @param \Tests\Clients\TestCaseClient $client
     */
    public function testPathParam(TestCaseClient $client): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $client->invalidPathParam([]);
    }

    /**
     * @depends testCreatingInstance
     * @param \Tests\Clients\TestCaseClient $client
     */
    public function testQueryParams(TestCaseClient $client): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $client->invalidQueryParams(1);
    }

    /**
     * @depends testCreatingInstance
     * @param \Tests\Clients\TestCaseClient $client
     */
    public function testHeaders(TestCaseClient $client): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $client->invalidHeaders(1, '');
    }

    /**
     * @depends testCreatingInstance
     * @param \Tests\Clients\TestCaseClient $client
     */
    public function testHeaderParams(TestCaseClient $client): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $client->invalidHeaderParam(1, []);
    }

    /**
     * @depends testCreatingInstance
     * @param \Tests\Clients\TestCaseClient $client
     */
    public function testMultipart(TestCaseClient $client): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $client->invalidMultipart(1);
    }
}