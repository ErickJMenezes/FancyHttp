# Quickstart

Introducing the FancyHttp and some usage examples.

## Creating a client

### Creating an interface

```php
<?php // FooClient.php

namespace App\Clients;

use ErickJMenezes\FancyHttp\Attributes\Get;
use ErickJMenezes\FancyHttp\Attributes\PathParam;

interface FooClient
{
    #[Get('foo')]
    public function getAll(): array;
    
    #[Get('foo/{id}')]
    public function getById(#[PathParam('id')] int $id): array;
}
```

In FancyHttp, a client is just a regular PHP interface. We use the PHP 8 Attributes to give the information about how
the FancyHttp must make the request to the api.  

### Creating an instance

```php
<?php // FooController.php

namespace App\Controllers;

use App\Clients\FooClient;
use ErickJMenezes\FancyHttp\Client;

class FooController
{
    protected FooClient $fooClient;
    
    public function __construct() 
    {
        $this->fooClient = Client::createForInterface(
            FooClient::class,
            'https://api.fooapp.com/'
        );
    }
}
```

To make use of FooClient, we just ask to FancyHttp to generate an implementation
for that interface. The `ErickJMenezes\FancyHttp\Client` class can really generate
a concrete implementation for the interface provided in the first argument, this
means you can safely assign to a typed property or pass to any typed parameter of
any function.

### The client

```php
use ErickJMenezes\FancyHttp\Client;
```
This class provides only one method: `Client::createForInterface()`  
the method accepts three arguments, the first is the **fully qualified interface name**, 
the second is the **base uri** according to 
[RFC 3986](https://tools.ietf.org/html/rfc3986#section-5.2) (optional),
and the third is an array with guzzle options (optional).

```php
use ErickJMenezes\FancyHttp\Client;

$client = Client::createForInterface(
    YourInterface::class,
    'https://your.base-uri.com/api/'
);
```

### Sending a request

To send a request, you just need to call the method of your client
like any regular object, and the response is automatically casted 
to the type delcared in the interface.

```php
<?php // FooController.php

namespace App\Controllers;

use App\Clients\FooClient;
use ErickJMenezes\FancyHttp\Client;

class FooController
{
    protected FooClient $fooClient;
    
    public function __construct() {
        $this->fooClient = Client::createForInterface(
            FooClient::class,
            'https://api.fooapp.com/'
        );
    }
    
    public function find($id)
    {
        // Just call the declared method..
        return $this->fooClient->getById($id);
    }
}
```

### Return types

FancyHttp allows the client interface methods to have different return types:  

- array
- string
- int
- void
- object
- \ArrayObject
- \Psr\Http\Message\ResponseInterface
- mixed
- no type
- \ErickJMenezes\FancyHttp\Castable (special type)
- AutoMapped Interface (special type)

*Work in progress*