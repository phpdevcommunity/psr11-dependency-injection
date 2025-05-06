# PSR-11 Dependency Injection Container

A lightweight PHP Dependency Injection Container implementing the PSR-11 standard. This library is designed for simplicity and ease of use, making it an ideal choice for small projects where you need a quick and effective DI solution.

## Installation

```bash
composer require phpdevcommunity/psr11-dependency-injection
```

## Usage

### 1. Define Your Services

Create a `services.php` file where you can define the parameters and services your application needs:

```php
<?php

use Psr\Container\ContainerInterface;

return [
    'database.host' => '127.0.0.1',
    'database.port' => null,
    'database.name' => 'my_database',
    'database.user' => 'root',
    'database.password' => null,
    'google.key' => 'YQ4FcwaXD165Xm72lx53qzzNzkz7AUUN',
    PDO::class => static function (ContainerInterface $container) {
        return new PDO(
            sprintf('mysql:host=%s;dbname=%s;', $container->get('database.host'), $container->get('database.name')),
            $container->get('database.user'),
            $container->get('database.password'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    },
];
```

### 2. Create and Use the Container

Instantiate the `Container` class with your service definitions and retrieve your services:

```php
<?php

use PhpDevCommunity\DependencyInjection\Container;

$services = require 'services.php';
$container = new Container($services);

// Retrieve the PDO instance
$pdo = $container->get(PDO::class);
var_dump($pdo); // Outputs: object(PDO)[18]

// Retrieve a simple configuration value
$googleKey = $container->get('google.key');
var_dump($googleKey); // Outputs: YQ4FcwaXD165Xm72lx53qzzNzkz7AUUN
```

### 3. Autowiring

This library includes support for **autowiring**, allowing the container to automatically resolve class dependencies without the need for manual service definitions. The `ReflectionResolver` class leverages PHP’s Reflection API to inspect the constructor of a class and inject the necessary dependencies.

#### Example: Using Autowiring

```php
<?php

use PhpDevCommunity\DependencyInjection\Container;
use PhpDevCommunity\DependencyInjection\ReflectionResolver;
use App\Service\MyService;

$services = require 'services.php';
$container = new Container($services, new ReflectionResolver());

// Automatically resolve MyService and its dependencies
$myService = $container->get(MyService::class);
var_dump($myService); // Outputs: object(MyService)
```


### 4. Variable Replacement in Configuration

This container also supports the replacement of variables in configuration values. This is useful when you want to store environment-specific settings, like database credentials, that can reference other configuration parameters. You can define variables in your configuration files that are automatically replaced when the container retrieves a service.

#### Example: Variable Replacement

```php
<?php

use Psr\Container\ContainerInterface;

return [
    'database.host' => '127.0.0.1',
    'database.port' => '3306',
    'database.user' => 'root',
    'database.dsn'  => 'mysql://#{database.user}@#{database.host}:#{database.port}/mydb',
];
```

In this example, the `database.dsn` value contains references to other configuration keys. When you retrieve this value from the container, the variables are automatically replaced with their corresponding values, resulting in the correct `dsn` for connecting to the database:

```php
<?php

use PhpDevCommunity\DependencyInjection\Container;

$services = require 'services.php';
$container = new Container($services);

// Retrieve the database DSN with variables replaced
$dsn = $container->get('database.dsn');
echo $dsn; // Outputs: mysql://root@127.0.0.1:3306/mydb
```

This makes it easy to manage complex configurations that depend on other configuration values.

## Contributing

Contributions are welcome! Feel free to open issues or submit pull requests to help improve the library.

## License

This library is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
