<?php


namespace Tests;


use PHPUnit\Framework\TestCase;
use Tests\Clients\ClientSetup;

/**
 * Class AttributeTypeExpectationsTest
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests
 * @covers
 */
class AttributeTypeExpectationsTest extends TestCase
{
    use ClientSetup;

    public function testPathParam(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->invalidPathParam([]);
    }

    public function testQueryParams(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->invalidQueryParams(1);
    }

    public function testHeaders(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->invalidHeaders(1, '');
    }

    public function testHeaderParams(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->invalidHeaderParam(1, []);
    }

    public function testMultipart(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client->invalidMultipart(1);
    }
}