<?php


namespace Tests;


use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Tests\Clients\ClientSetup;

/**
 * Class VerbsTest
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests
 * @coversNothing
 */
class VerbsTest extends TestCase
{
    use ClientSetup;

    protected function createExpectation(string $method)
    {
        $this->handler->append(function (RequestInterface $request) use ($method) {
            if ($request->getMethod() === $method) {
                return new Response();
            } else {
                return $this->createClientError("$method expected, {$request->getMethod()} given.", $request, 400);
            }
        });
    }

    protected function expectsClientError(): void
    {
        $this->expectException(ClientException::class);
    }

    public function testGet()
    {
        $this->createExpectation('GET');
        $status = $this->client->get();
        self::assertTrue($status === 200);
        $this->expectsClientError();
        $this->createExpectation('POST');
        $this->client->get();
    }

    public function testPost()
    {
        $this->createExpectation('POST');
        $status = $this->client->post();
        self::assertTrue($status === 200);
    }

    public function testPut()
    {
        $this->createExpectation('PUT');
        $status = $this->client->put();
        self::assertTrue($status === 200);
    }

    public function testPatch()
    {
        $this->createExpectation('PATCH');
        $status = $this->client->patch();
        self::assertTrue($status === 200);
    }

    public function testDelete()
    {
        $this->createExpectation('DELETE');
        $status = $this->client->delete();
        self::assertTrue($status === 200);
    }

    public function testHead()
    {
        $this->createExpectation('HEAD');
        $status = $this->client->head();
        self::assertTrue($status === 200);
    }
}