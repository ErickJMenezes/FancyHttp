<?php declare(strict_types=1);


namespace Tests;

use ErickJMenezes\FancyHttp\Client;
use PHPUnit\Framework\TestCase;
use Tests\Clients\TestCaseClient;

/**
 * Class VerbsAndSomeParametersTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class SimpleCrudTest extends TestCase
{
    public function testCreatingInstance(): TestCaseClient
    {
        $instance = Client::createFromInterface(TestCaseClient::class, 'https://jsonplaceholder.typicode.com/');
        $this->assertTrue($instance instanceof TestCaseClient, 'instance not created');
        return $instance;
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testGetTodos(TestCaseClient $client)
    {
        $response = $client->getTodos();
        $user = $response[0];
        $this->assertArrayHasKey('id', $user);
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testGetTodoById(TestCaseClient $client)
    {
        $response = $client->getTodoById(1);
        self::assertArrayHasKey('id', $response, 'Todo must have an id');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testGetTodoByIdStringable(TestCaseClient $client)
    {
        $response = $client->getTodoByIdStringableParam(new class implements \Stringable {
            public function __toString(): string {return '1';}
        });
        self::assertTrue($response);
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testCreateTodo(TestCaseClient $client)
    {
        $response = $client->createTodo([
            'userId' => 1,
            'title' => 'test case',
            'completed' => true
        ]);
        $this->assertArrayHasKey('id', $response, 'Response must have a id');
        $this->assertArrayHasKey('userId', $response, 'Response must have a userId');
        $this->assertArrayHasKey('completed', $response, 'Response must have a completed state');
        $this->assertArrayHasKey('title', $response, 'Response must have a title');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testUpdateTodo(TestCaseClient $client)
    {
        $response = $client->getTodoById(1);
        $this->assertArrayHasKey('id', $response, 'Todo must have an id');
        $response['title'] = 'testCase';
        $updatedResponse = $client->updateTodo(1, $response);
        self::assertTrue($updatedResponse['title'] === $response['title'], 'Response is not updated');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testDeleteTodo(TestCaseClient $client)
    {
        $response = $client->deleteTodo(1);
        self::assertTrue($response, 'Todo not deleted');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testFilterTodosWithQueryString(TestCaseClient $client)
    {
        $response = $client->getTodos(['id' => 1]);
        self::assertTrue(count($response) === 1, 'Response is not filtered');
        $user = $response[0];
        $this->assertArrayHasKey('id', $user, 'Response is invalid');
    }

    /**
     * @param \Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testQueryParamPlusQueryStringParameters(TestCaseClient $client)
    {
        $response = $client->getUserTodos(1, ['id' => 3]);
        self::assertTrue(count($response) === 1, 'Response must have a size of 1');
        self::assertTrue($response[0]['id'] === 3, 'TodoId is incorrect.');
        self::assertTrue($response[0]['userId'] === 1, 'UserId is incorrect.');
    }

    /**
     * @depends testCreatingInstance
     * @param \Tests\Clients\TestCaseClient $client
     */
    public function testSuppressedError(TestCaseClient $client)
    {
        $response = $client->getTodoByIdSuppressed(99999);
        self::assertTrue($response->getStatusCode() === 404);
        self::assertTrue($client->lastResponse->getStatusCode() === 404);
    }
}