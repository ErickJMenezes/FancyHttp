<?php


namespace Tests\Clients;

use FancyHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Trait ClientSetup
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests\Clients
 */
trait ClientSetup
{
    protected TestCaseClient $client;
    protected MockHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new MockHandler();
        $this->client = Client::createForInterface(
            TestCaseClient::class,
            guzzleOptions: [
                'handler' => $this->handler
            ]
        );
    }

    protected function createClientError(string $message, RequestInterface $request, int $statusCode): ClientException
    {
        return new ClientException($message, $request, new Response($statusCode));
    }
}