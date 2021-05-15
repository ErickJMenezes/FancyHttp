<?php


namespace Tests;


use FancyHttp\Client;
use PHPUnit\Framework\TestCase;
use Tests\Clients\TestCaseClient;

/**
 * Class BenchmarkTest
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests
 * @covers
 */
class BenchmarkTest extends TestCase
{
    public function testCreatingThousandInstances(): void
    {
        $timeStart = microtime(true);
        for ($i = 0; $i < 10000; $i++) {
            Client::createForInterface(TestCaseClient::class);
        }
        $timeEnd = microtime(true);
        $time = $timeEnd - $timeStart;
        self::assertTrue($time <= 0.5);
    }
}