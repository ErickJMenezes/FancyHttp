<?php

namespace Tests\Clients;

use ErickJMenezes\Http\Attributes\Api;
use ErickJMenezes\Http\Attributes\Body;
use ErickJMenezes\Http\Attributes\Delete;
use ErickJMenezes\Http\Attributes\Get;
use ErickJMenezes\Http\Attributes\HeaderParam;
use ErickJMenezes\Http\Attributes\Patch;
use ErickJMenezes\Http\Attributes\PathParam;
use ErickJMenezes\Http\Attributes\Post;
use ErickJMenezes\Http\Attributes\Put;
use ErickJMenezes\Http\Attributes\QueryParams;
use Psr\Http\Message\ResponseInterface;


#[Api(baseUri: 'https://jsonplaceholder.typicode.com/')]
interface TestCaseClient
{
    #[Get('todos')]
    public function getTodos(#[QueryParams] array $query = []): array;

    #[Get('todos/{id}')]
    public function getTodoById(#[PathParam('id')] int $id): array;

    #[Get('todos/{id}')]
    public function getTodoByIdStringableParam(#[PathParam('id')] \Stringable $id): bool;

    #[Post('todos')]
    public function createTodo(#[Body(Body::TYPE_JSON)] array $body): array;

    #[Patch('todos/{id}')]
    public function updateTodo(
        #[PathParam('id')] int $id,
        #[Body(Body::TYPE_JSON)] array $body
    ): array;

    #[Put('todos/{id}')]
    public function replaceTodo(
        #[PathParam('id')] int $id,
        #[Body(Body::TYPE_JSON)] array $body
    ): array;

    #[Delete('todos/{id}')]
    public function deleteTodo(#[PathParam('id')] int $id): bool;

    #[Get('users/{id}/todos')]
    public function getUserTodos(
        #[PathParam('id')] int $id,
        #[QueryParams] array $query = []
    ): array;

    // Casting return types test.

    #[Get('todos/{id}')]
    public function getTodoByIdArray(#[PathParam('id')] int $id): array;

    #[Get('todos/{id}')]
    public function getTodoByIdObject(#[PathParam('id')] int $id): object;

    #[Get('todos/{id}')]
    public function getTodoByIdResponseInterface(#[PathParam('id')] int $id): ResponseInterface;

    #[Get('todos/{id}')]
    public function getTodoByIdVoid(#[PathParam('id')] int $id): void;

    #[Get('todos/{id}')]
    public function getTodoByIdString(#[PathParam('id')] int $id): string;

    #[Get('todos/{id}')]
    public function getTodoByIdBoolean(#[PathParam('id')] int $id): bool;

    #[Get('todos/{id}')]
    public function getTodoByIdMixed(#[PathParam('id')] int $id): mixed;

    #[Get('todos/{id}')]
    public function getTodoByIdNone(#[PathParam('id')] int $id);
}