<?php declare(strict_types = 1);


use ErickJMenezes\Http\Client;
use PHPUnit\Framework\TestCase;
use Tests\Clients\TestClient;

/**
 * Class ClientTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class ClientTest extends TestCase
{
    /**
     * @dataProvider clientProvider
     * @param \ErickJMenezes\Http\Client $client
     */
    public function testCreatingInstance(Client $client)
    {
        $this->assertTrue($client::class === Client::class);
    }

    public function clientProvider(): Client
    {
        return Client::createFromInterface(TestClient::class);
    }
}