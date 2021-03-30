# Fancy HTTP
#### Just declare the client interface and you are ready to use it! 

Look the example down below:
~~~php
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Client;

/**
* Interface TodosClient
 * 
* @author ErickJMenezes <erickmenezes.dev@gmail.com>
*/
interface TodosClient {

    /**
    * @param int $id The path parameter.
    * @return \ArrayObject The response will be automatically casted to "ArrayObject"
    */
    #[Get('todos/{id}')] // The endpoint
    public function getTodoById(#[PathParam('id')] int $id): \ArrayObject;
    
    /**
    * @return array The response will be automatically casted to "array"
    */
    #[Get('todos')]
    public function getTodos(): array;
}

// The Client class accepts two parameters, the first is a
// fully qualified interface name and the second is the base uri.
// The Client class will create a real instance of TodosClient, you can
// safely assign to a typed parameter.
$todoClient = Client::createFromInterface(TodosClient::class, 'http://api.yourdomain.etc/');

// Now we have everything we need to use our client.
// Call the method declared in TodosClient.
$todo = $todoClient->getTodoById(1);

// Do something with the response...
printf($todo->title);
~~~
It's simple as that!

## How to install?
~~~shell
$ composer require erickjmenezes/fancyhttp
~~~

## How to test?
~~~shell
$ php8.0 vendor/bin/phpunit
~~~

## Documentation
Soon...
