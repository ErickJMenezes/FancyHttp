# Fancy HTTP
#### A http client to implements your clients interfaces for you! 

Look the example down below:
~~~php
use ErickJMenezes\FancyHttp\Attributes\Api;
use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\PathParam;
use ErickJMenezes\FancyHttp\Client;

#[Api]
interface MyClient {
    #[Get('todos/{id}')]
    public function getTodoById(
        #[PathParam('id')] int $id
    ): array; // the response will be automatically casted to array.
}

$myClient = Client::createFromInterface(
    MyClient::class, // the fully qualified interface name.
    'http://localhost:9000/api/' // The webservice base uri.
);

// Call the method declared in interface.
$todo = $myClient->getTodoById(1);

echo $todo['description']; // echo the todo's description
~~~
It's simple as that!

## How to install?
(In development)
~~~shell
$ composer require erickjmenezes/fancyhttp
~~~

## Documentation
Soon...
