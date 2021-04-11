<?php


namespace Tests;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Tests\Clients\ClientSetup;

/**
 * Class HeadersTest
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests
 * @coversNothing
 */
class HeadersTest extends TestCase
{
    use ClientSetup;

    public function testHeaderAttribute()
    {
        $mock = function (RequestInterface $request) {
            if ($request->hasHeader('X-Foo')) return new Response();
            return new Response(400);
        };
        $this->handler->append($mock, $mock);
        $status = $this->client->headersAttribute(['X-Foo' => 'bar']);
        self::assertTrue($status === 200);
        $status = $this->client->headersAttribute([]);
        self::assertTrue($status === 400);
    }

    public function testHeaderParamAttribute()
    {
        $mock = function (RequestInterface $request) {
            if ($request->hasHeader('X-Foo') && !empty($request->getHeaderLine('X-Foo')))
                return new Response();
            return new Response(400);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->headerParamAttribute('bar');
        self::assertTrue($status === 200);

        $status = $this->client->headerParamAttribute();
        self::assertTrue($status === 400);
    }

    public function testBearer()
    {
        $mock = function (RequestInterface $request) {
            if ($request->hasHeader('Authorization') &&
            $request->getHeaderLine('Authorization') === 'Bearer t'
            )
                return new Response();
            return new Response(400);
        };
        $this->handler->append($mock);

        $status = $this->client->bearer();
        self::assertTrue($status === 200);
    }

    public function testBasic()
    {
        $mock = function (RequestInterface $request) {
            if ($request->hasHeader('Authorization') &&
                $request->getHeaderLine('Authorization') === 'Basic YTpi'
            )
                return new Response();
            return new Response(401);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->basic(['a', 'b']);
        self::assertTrue($status === 200);

        $status = $this->client->basic(['a', 'bc']);
        self::assertTrue($status === 401);

        $this->expectException(InvalidArgumentException::class);
        $this->client->basic(['a']);
    }

    public function testDigest()
    {
        $mock = function (RequestInterface $request, array $options) {
            [$login, $passw, $type] = $options['auth'] + ['', '', ''];
            if ("{$login}:{$passw}:{$type}" === 'demo:demo:digest')
                return new Response();

            return new Response(401);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->digest(['demo', 'demo']);
        self::assertTrue($status === 200);

        $status = $this->client->digest(['a', 'b']);
        self::assertTrue($status === 401);

        $this->expectException(InvalidArgumentException::class);
        $this->client->digest(['a']);
    }

    public function testNtml()
    {
        $mock = function (RequestInterface $request, array $options) {
            [$login, $passw, $type] = $options['auth'] + ['', '', ''];
            if ("{$login}:{$passw}:{$type}" === 'demo:demo:ntml')
                return new Response();

            return new Response(401);
        };
        $this->handler->append($mock, $mock);

        $status = $this->client->ntml(['demo', 'demo']);
        self::assertTrue($status === 200);

        $status = $this->client->ntml(['a', 'b']);
        self::assertTrue($status === 401);

        $this->expectException(InvalidArgumentException::class);
        $this->client->ntml(['a']);
    }
}