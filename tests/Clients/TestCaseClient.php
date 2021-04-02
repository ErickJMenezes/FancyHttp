<?php

namespace Tests\Clients;

use ErickJMenezes\FancyHttp\Attributes\Body;
use ErickJMenezes\FancyHttp\Attributes\Delete;
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\HeaderParam;
use ErickJMenezes\FancyHttp\Attributes\Headers;
use ErickJMenezes\FancyHttp\Attributes\Multipart;
use ErickJMenezes\FancyHttp\Attributes\Patch;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Attributes\Post;
use ErickJMenezes\FancyHttp\Attributes\Put;
use ErickJMenezes\FancyHttp\Attributes\QueryParams;
use ErickJMenezes\FancyHttp\Attributes\ReturnsMappedList;
use ErickJMenezes\FancyHttp\Attributes\Suppress;
use Psr\Http\Message\ResponseInterface;


/**
 * Interface TestCaseClient
 *
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests\Clients
 * @property-read ResponseInterface $lastResponse
 */
interface TestCaseClient
{
    #[Get('todos')]
    public function getTodos(#[QueryParams] array $query = []): array;

    #[Get('todos/{id}')]
    public function getTodoById(#[PathParam('id')] int $id): array;

    #[Get('todos/{id}')]
    public function getTodoByIdStringableParam(#[PathParam('id')] \Stringable $id): bool;

    #[Get('todos/{id}')]
    #[Suppress]
    public function getTodoByIdSuppressed(#[PathParam('id')] int $id): ResponseInterface;

    #[Post('todos')]
    public function createTodo(#[Body(Body::JSON)] array $body): array;

    #[Patch('todos/{id}')]
    public function updateTodo(
        #[PathParam('id')] int $id,
        #[Body(Body::JSON)] array $body
    ): array;

    #[Put('todos/{id}')]
    public function replaceTodo(
        #[PathParam('id')] int $id,
        #[Body(Body::JSON)] array $body
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

    #[Get('todos/{id}')]
    public function getTodoByIdCastable(#[PathParam('id')] int $id): CastableForTesting;

    #[Get('todos/{id}')]
    public function getTodoByIdMapped(#[PathParam('id')] int $id): TodoInterface;

    #[Get('todos')]
    #[ReturnsMappedList(TodoInterface::class)]
    public function getTodosMapped(): array;

    #[Get('todos/{id1}/tests/{id2}')]
    public function testMultiplePaths(
        #[PathParam('id1')] $id1,
        #[PathParam('id2')] $id2
    ): array;

    #[Get('todos/{id}')]
    public function invalidPathParam(#[PathParam('id')] array $id): int;

    #[Get('todos')]
    public function invalidQueryParams(
        #[QueryParams] int $query
    ): int;

    #[Get('todos/{id}')]
    public function invalidHeaders(
        #[PathParam('id')] int $id,
        #[Headers] string $headers
    ): int;

    #[Get('todos/{id}')]
    public function invalidHeaderParam(
        #[PathParam('id')] int $id,
        #[HeaderParam('Authorization')] array $bearer
    ): int;

    #[Get('todos')]
    public function invalidMultipart(
        #[Multipart] int $parts
    ): int;
}