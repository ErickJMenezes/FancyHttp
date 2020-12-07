<?php declare(strict_types = 1);


use ErickJMenezes\Http\Client;
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
    /**
     * @return \ErickJMenezes\Http\Client|mixed|\Tests\Clients\TestCaseClient
     */
    public function testCreatingInstance()
    {
        $instance = Client::createFromInterface(TestCaseClient::class);
        $this->assertTrue((bool)$instance, 'instance not created');
        return $instance;
    }

    /**
     * @param \ErickJMenezes\Http\Client|mixed|\Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testGetTodos($client)
    {
        $response = $client->getTodos();
        $this->assertIsArray($response, 'response is not array');
        $user = $response[0];
        $this->assertArrayHasKey('id', $user);
    }

    /**
     * @param \ErickJMenezes\Http\Client|mixed|\Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testGetTodoById($client)
    {
        $response = $client->getTodoById(1);
        $this->assertIsArray($response, 'response is not array');
        self::assertArrayHasKey('id', $response, 'Todo must have an id');
    }

    /**
     * @param \ErickJMenezes\Http\Client|mixed|\Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testCreateTodo($client)
    {
        $response = $client->createTodo([
            'userId' => 1,
            'title' => 'test case',
            'completed' => true
        ]);
        $this->assertIsArray($response, 'response is not array');
        $this->assertArrayHasKey('id', $response, 'Response must have a id');
        $this->assertArrayHasKey('userId', $response, 'Response must have a userId');
        $this->assertArrayHasKey('completed', $response, 'Response must have a completed state');
        $this->assertArrayHasKey('title', $response, 'Response must have a title');
    }

    /**
     * @param \ErickJMenezes\Http\Client|mixed|\Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testUpdateTodo($client)
    {
        $response = $client->getTodoById(1);
        $this->assertIsArray($response, 'response is not array');
        $this->assertArrayHasKey('id', $response, 'Todo must have an id');
        $response['title'] = 'testCase';
        $updatedResponse = $client->updateTodo(1, $response);
        self::assertTrue($updatedResponse['title'] === $response['title'], 'Response is not updated');
    }

    /**
     * @param \ErickJMenezes\Http\Client|mixed|\Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testDeleteTodo($client)
    {
        $response = $client->deleteTodo(1);
        self::assertTrue($response, 'Todo not deleted');
    }

    /**
     * @param \ErickJMenezes\Http\Client|mixed|\Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testFilterTodosWithQueryString($client)
    {
        $response = $client->getTodos(['id' => 1]);
        self::assertTrue(count($response) === 1, 'Response is not filtred');
        $user = $response[0];
        $this->assertArrayHasKey('id', $user, 'Response is invalid');
    }

    /**
     * @param \ErickJMenezes\Http\Client|mixed|\Tests\Clients\TestCaseClient $client
     * @depends testCreatingInstance
     */
    public function testQueryParamPlusQueryStringParameters($client)
    {
        $response = $client->getUserTodos(1, ['id' => 3]);
        self::assertIsArray($response, 'Response must be a array');
        self::assertTrue(count($response) === 1, 'Response must have a size of 1');
        self::assertTrue($response[0]['id'] === 3, 'TodoId is incorrect.');
        self::assertTrue($response[0]['userId'] === 1, 'UserId is incorrect.');
    }
}