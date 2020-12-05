<?php

namespace ErickJMenezes\Http\Clients;

use ErickJMenezes\Http\Attributes\Api;
use ErickJMenezes\Http\Attributes\Body;
use ErickJMenezes\Http\Attributes\Get;
use ErickJMenezes\Http\Attributes\HeaderParam;
use ErickJMenezes\Http\Attributes\PathParam;
use ErickJMenezes\Http\Attributes\Post;
use ErickJMenezes\Http\Attributes\QueryParams;
use Psr\Http\Message\ResponseInterface;


#[Api('https://jsonplaceholder.typicode.com/')]
interface TypicodeClient
{
    #[Get('todos')]
    public function getTodos(#[QueryParams] array $query = []): array;

    #[Post('todos')]
    public function createTodo(#[Body(Body::TYPE_JSON)] array $body): array;

    #[Get('posts')]
    public function getPosts(#[QueryParams] array $query = []): array;

    #[Get('users')]
    public function getUsers(#[QueryParams] array $query = []): array;

    #[Get('albums')]
    public function getAlbums(#[QueryParams] array $query = []): array;

    #[Get('todos/{id}')]
    public function getTodoById(#[PathParam('id')] int $id): array;

    #[Get('posts/{id}')]
    public function getPostById(#[PathParam('id')] int $id): array;

    #[Get('users/{id}')]
    public function getUserById(#[PathParam('id')] int $id): array;

    #[Get('albums/{id}')]
    public function getAlbumById(#[PathParam('id')] int $id): array;

    #[Get('posts/{id}/comments')]
    public function getPostComments(
        #[PathParam('id')] int $postId,
        #[QueryParams] array $query = []
    ): array;

    #[Get('albums/{id}/photos')]
    public function getAlbumPhotos(
        #[PathParam('id')] int $postId,
        #[QueryParams] array $query = []
    ): array;

    #[Get('users/{id}/albums')]
    public function getUserAlbums(
        #[PathParam('id')] int $postId,
        #[QueryParams] array $query = []
    ): array;

    #[Get('users/{id}/todos')]
    public function getUserTodos(
        #[PathParam('id')] int $postId,
        #[QueryParams] array $query = []
    ): array;

    #[Get('users/{id}/posts')]
    public function getUserPosts(
        #[PathParam('id')] int $postId,
        #[QueryParams] array $query = []
    ): array;

    #[Get('posts/{postId}/comments/{commentId}')]
    public function getPostCommentById(
        #[PathParam('postId')] int $postId,
        #[PathParam('commentId')] int $commentId
    ): array;

    #[Get('albums/{albumId}/photos/{photoId}')]
    public function getAlbumPhotoById(
        #[PathParam('albumId')] int $albumId,
        #[PathParam('photoId')] int $photoId
    ): array;

    #[Get('users/{userId}/albums/{albumId}')]
    public function getUserAlbumById(
        #[PathParam('userId')] int $userId,
        #[PathParam('albumId')] int $albumId
    ): array;

    #[Get('users/{userId}/todos/{todoId}')]
    public function getUserTodoById(
        #[PathParam('userId')] int $postId,
        #[PathParam('todoId')] int $todoId
    ): array;

    #[Get('users/{userId}/posts/{postId}')]
    public function getUserPostById(
        #[PathParam('userId')] int $userId,
        #[PathParam('postId')] int $postId
    ): array;
}