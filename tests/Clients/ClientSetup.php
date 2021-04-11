<?php


namespace Tests\Clients;

use ErickJMenezes\FancyHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
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
        $this->client = Client::createFromInterface(
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

    protected function setUpHeadersHandler(): MockHandler
    {
        $headerXFoo = function (RequestInterface $request){
            if ($request->hasHeader('X-Foo')) {
                return new Response();
            } else {
                return $this->createClientError('The header X-Foo is missing', $request, 422);
            }
        };

        $bearer = function (RequestInterface $request) {
            if (
                $request->hasHeader('Authorization') &&
                \str_starts_with($request->getHeaderLine('Auhtorization'), 'Bearer ')
            ) {
                return new Response();
            } else {
                return $this->createClientError('Unauthorized', $request, 401);
            }
        };

        return new MockHandler([
            $headerXFoo,
            $bearer
        ]);
    }
}