<?php


namespace Tests\Clients;

use ErickJMenezes\FancyHttp\Client;

/**
 * Trait ClientSetup
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests\Clients
 */
trait ClientSetup
{
    protected TestCaseClient $client;

    protected function setUp(): void
    {
        $this->client = Client::createFromInterface(
            TestCaseClient::class,
            'https://jsonplaceholder.typicode.com/'
        );
    }
}