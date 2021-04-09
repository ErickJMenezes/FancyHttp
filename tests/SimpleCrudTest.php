<?php declare(strict_types=1);


namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Clients\ClientSetup;

/**
 * Class VerbsAndSomeParametersTest
 *
 * @author ErickJMenezes <erickmenezes.dev@gmail.com>
 * @covers
 */
class SimpleCrudTest extends TestCase
{
    use ClientSetup;

    public function testGetTodos()
    {
        $response = $this->client->getTodos();
        $user = $response[0];
        $this->assertArrayHasKey('id', $user);
    }

    public function testGetTodoById()
    {
        $response = $this->client->getTodoById(1);
        self::assertArrayHasKey('id', $response, 'Todo must have an id');
    }

    public function testGetTodoByIdStringable()
    {
        $response = $this->client->getTodoByIdStringableParam(new class implements \Stringable {
            public function __toString(): string {return '1';}
        });
        self::assertTrue($response);
    }

    public function testCreateTodo()
    {
        $response = $this->client->createTodo([
            'userId' => 1,
            'title' => 'test case',
            'completed' => true
        ]);
        $this->assertArrayHasKey('id', $response, 'Response must have a id');
        $this->assertArrayHasKey('userId', $response, 'Response must have a userId');
        $this->assertArrayHasKey('completed', $response, 'Response must have a completed state');
        $this->assertArrayHasKey('title', $response, 'Response must have a title');
    }

    public function testUpdateTodo()
    {
        $response = $this->client->getTodoById(1);
        $this->assertArrayHasKey('id', $response, 'Todo must have an id');
        $response['title'] = 'testCase';
        $updatedResponse = $this->client->updateTodo(1, $response);
        self::assertTrue($updatedResponse['title'] === $response['title'], 'Response is not updated');
    }

    public function testDeleteTodo()
    {
        $response = $this->client->deleteTodo(1);
        self::assertTrue($response, 'Todo not deleted');
    }

    public function testFilterTodosWithQueryString()
    {
        $response = $this->client->getTodos(['id' => 1]);
        self::assertTrue(count($response) === 1, 'Response is not filtered');
        $user = $response[0];
        $this->assertArrayHasKey('id', $user, 'Response is invalid');
    }

    public function testQueryParamPlusQueryStringParameters()
    {
        $response = $this->client->getUserTodos(1, ['id' => 3]);
        self::assertTrue(count($response) === 1, 'Response must have a size of 1');
        self::assertTrue($response[0]['id'] === 3, 'TodoId is incorrect.');
        self::assertTrue($response[0]['userId'] === 1, 'UserId is incorrect.');
    }

    public function testSuppressedError()
    {
        $response = $this->client->getTodoByIdSuppressed(99999);
        self::assertTrue($response->getStatusCode() === 404);
        self::assertTrue($this->client->lastResponse->getStatusCode() === 404);
    }
}