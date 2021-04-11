<?php


namespace Tests;


use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Tests\Clients\ClientSetup;

/**
 * Class InstructionAttributesTest
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests
 * @coversNothing
 */
class InstructionAttributesTest extends TestCase
{
    use ClientSetup;

    public function testUnwrap()
    {
        $this->handler->append(
            new Response(body: json_encode(['data' => ['foo' => 'bar']]))
        );

        $response = $this->client->unwrap();
        $this->assertTrue($response === ['foo' => 'bar']);
    }

    public function testSuppress()
    {
        $this->handler->append(function (RequestInterface $request, array $options) {
            if ($options['http_errors'])
                return new ClientException('error', $request, new Response(400));
            return new Response(400);
        });

        $response = $this->client->suppress();
        self::assertTrue($response === 400);
    }
}