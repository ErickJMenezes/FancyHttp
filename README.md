# Fancy HTTP
#### Just declare the client interface, and you are ready to use it! 

Look the example down below:
~~~php
use ErickJMenezes\FancyHttp\Attributes\Api;
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Client;

/**
 * To-do list microservice interface
 */
#[Api] // Api attribute
interface MyClient {

    /**
    * @param int $id The id will be used to replace the path parameter "id".
    * @return array The Response will be automatically casted to the type
    *               declared in the method's signature.
    */
    #[Get('todos/{id}')] // The endpoint
    public function getTodoById(#[PathParam('id')] int $id): array;
}

// The Client class accepts two parameters, the first is a
// fully qualified interface name and the second is the base uri.
$myClient = Client::createFromInterface(MyClient::class, 'http://localhost:9000/api/');

// Now we have everything we need to use our client.
// Call the method declared in MyClient.
$todo = $myClient->getTodoById(1);

// Do something with the response...
printf($todo['title']);
~~~
It's simple as that!

## How to install?
~~~shell
$ composer require erickjmenezes/fancyhttp
~~~

## Documentation
Soon...
