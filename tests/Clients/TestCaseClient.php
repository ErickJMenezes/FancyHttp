<?php

namespace Tests\Clients;

use ErickJMenezes\FancyHttp\Attributes\Auth\Basic;
use ErickJMenezes\FancyHttp\Attributes\Auth\Bearer;
use ErickJMenezes\FancyHttp\Attributes\Auth\Digest;
use ErickJMenezes\FancyHttp\Attributes\Auth\Ntml;
use ErickJMenezes\FancyHttp\Attributes\Body;
use ErickJMenezes\FancyHttp\Attributes\Delete;
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\Head;
use ErickJMenezes\FancyHttp\Attributes\HeaderParam;
use ErickJMenezes\FancyHttp\Attributes\Headers;
use ErickJMenezes\FancyHttp\Attributes\Json;
use ErickJMenezes\FancyHttp\Attributes\Multipart;
use ErickJMenezes\FancyHttp\Attributes\Patch;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Attributes\Post;
use ErickJMenezes\FancyHttp\Attributes\Put;
use ErickJMenezes\FancyHttp\Attributes\Query;
use ErickJMenezes\FancyHttp\Attributes\QueryParam;
use ErickJMenezes\FancyHttp\Attributes\ReturnsMappedList;
use ErickJMenezes\FancyHttp\Attributes\Suppress;
use ErickJMenezes\FancyHttp\Attributes\Unwrap;
use Psr\Http\Message\ResponseInterface;


/**
 * Interface TestCaseClient
 *
 * @property-read ResponseInterface|null $lastResponse
 * @author  ErickJMenezes <erickmenezes.dev@gmail.com>
 * @package Tests\Clients
 */
interface TestCaseClient
{
    // verbs test

    #[Get('/')]
    public function get(): int;

    #[Post('/')]
    public function post(): int;

    #[Put('/')]
    public function put(): int;

    #[Patch('/')]
    public function patch(): int;

    #[Delete('/')]
    public function delete(): int;

    #[Head('/')]
    public function head(): int;

    // headers test

    #[Get('/')]
    public function headersAttribute(#[Headers] array $headers): int;

    #[Get('/')]
    public function headerParamAttribute(#[HeaderParam('X-Foo')] string $value = null): int;

    #[Get('/')]
    public function bearer(#[Bearer] string $token = 't'): int;

    #[Get('/')]
    public function basic(#[Basic] array $auth): int;

    #[Get('/')]
    public function digest(#[Digest] array $auth): int;

    #[Get('/')]
    public function ntml(#[Ntml] array $auth): int;

    // sending data test

    #[Post('/')]
    public function body(#[Body] string $body): int;

    #[Post('/')]
    public function json(#[Json] array $json): int;

    #[Get('/')]
    public function query(#[Query] array $query): int;

    #[Get('/')]
    public function queryParams(#[QueryParam('foo')] string $foo, #[QueryParam('bar')] string $bar): int;

    #[Get('/{foo}/{bar}')]
    public function pathParams(#[PathParam('foo')] string $foo, #[PathParam('bar')] string $bar): int;

    #[Get('/')]
    public function multipart(#[Multipart] array $multipart): int;

    // casting response test

    #[Get('/')]
    public function castToArray(): array;

    #[Get('/')]
    public function castToObject(): object;

    #[Get('/')]
    public function castToBool(): bool;

    #[Get('/')]
    public function castToString(): string;

    #[Get('/')]
    public function castToInt(): int;

    #[Get('/')]
    public function castToArrayObject(): \ArrayObject;

    #[Get('/')]
    public function castToResponse(): ResponseInterface;

    #[Get('/')]
    public function castToVoid(): void;

    #[Get('/')]
    public function castToMixed(): mixed;

    #[Get('/')]
    public function castToDefault();

    #[Get('/')]
    public function castToCastable(): CastableForTesting;

    #[Get('/')]
    public function castToAutoMapped(): FooInterface;

    #[Get('/')]
    #[ReturnsMappedList(FooInterface::class)]
    public function castToAutoMappedList(): array;

    // Instruction attributes test

    #[Get('/')]
    #[Unwrap]
    public function unwrap(): array;

    #[Get('/')]
    #[Suppress]
    public function suppress(): int;
}