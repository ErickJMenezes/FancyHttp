<?php


namespace Tests;


use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Tests\Clients\ClientSetup;

/**
 * Class SendingDataTest
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests
 * @coversNothing
 */
class SendingDataTest extends TestCase
{
    use ClientSetup;

    public function testBody()
    {
        $mock = function (RequestInterface $request) {
            if ($request->getBody()->getContents() === 'body') return new Response();
            return new Response(400);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->body('body');
         self::assertTrue($status === 200);

        $status = $this->client->body('buddy');
        self::assertTrue($status === 400);
    }

    public function testJson()
    {
        $mock = function (RequestInterface $request) {
            if ($request->getBody()->getContents() === '{"data":1}') return new Response();
            return new Response(400);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->json(['data' => 1]);
        self::assertTrue($status === 200);

        $status = $this->client->json(['data' => 2]);
        self::assertTrue($status === 400);
    }

    public function testQuery()
    {
        $mock = function (RequestInterface $request) {
            if ($request->getUri()->getQuery() === 'foo=1&bar=2') return new Response();
            return new Response(400);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->query(['foo' => 1, 'bar' => 2]);
        self::assertTrue($status === 200);

        $status = $this->client->query(['bar' => 2]);
        self::assertTrue($status === 400);
    }

    public function testQueryParams()
    {
        $mock = function (RequestInterface $request) {
            if ($request->getUri()->getQuery() === 'foo=1&bar=2') return new Response();
            return new Response(400);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->queryParams('1', '2');
        self::assertTrue($status === 200);

        $status = $this->client->queryParams('2','1');
        self::assertTrue($status === 400);
    }

    public function testPathParams()
    {
        $mock = function (RequestInterface $request) {
            if ($request->getUri()->getPath() === '/1/2') return new Response();
            return new Response(400);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->pathParams('1', '2');
        self::assertTrue($status === 200);

        $status = $this->client->pathParams('2', '1');
        self::assertTrue($status === 400);
    }

    public function testMultipart()
    {
        $mock = function (RequestInterface $request) {
            if (str_starts_with($request->getHeaderLine('Content-Type'), 'multipart/form-data;'))
                return new Response();
            return new Response(400);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->multipart([
            [
                'name'     => 'foo',
                'contents' => 'data'
            ]
        ]);
        self::assertTrue($status === 200);
    }
}