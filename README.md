# OpenContainer

A dependency injection container for PHP, please enjoy responsibly.

[![travis-ci.org](https://travis-ci.org/modethirteen/OpenContainer.svg?branch=master)](https://travis-ci.org/modethirteen/OpenContainer)
[![codecov.io](https://codecov.io/github/modethirteen/OpenContainer/coverage.svg?branch=master)](https://codecov.io/github/modethirteen/OpenContainer?branch=master)
[![Latest Stable Version](https://poser.pugx.org/modethirteen/opencontainer/version.svg)](https://packagist.org/packages/modethirteen/opencontainer)

## About

OpenContainer was created as an attempt to leverage strict type checking available in full-featured PHP development environments, such as JetBrains PHPStorm, or the native strict type checking of PHP 7+, when managing dependencies from a centralized container. In addition, it contains some experiments with reflection and [proxies](https://github.com/Ocramius/ProxyManager) in order to avoid problems when circular dependencies are introduced in the container's dependency chain.

## Requirements

* PHP 7.4+

## Installation

Use [Composer](https://getcomposer.org/). There are two ways to add OpenContainer to your project.

From the composer CLI:

```sh
./composer.phar require modethirteen/opencontainer
```

Or add modethirteen/opencontainer to your project's composer.json:

```json
{
    "require": {
        "modethirteen/opencontainer": "dev-master"
    }
}
```

"dev-master" is the master development branch. If you are using OpenContainer in a production environment, it is advised that you use a stable release.

Assuming you have setup Composer's autoloader, OpenContainer can be found in the modethirteen\OpenContainer\ namespace.

## Adding OpenContainer to your application

Simply instantiate OpenContainer and you're ready to go.

```php
$container = new OpenContainer();
```

## Injectable Class

An injectable class is instantiated by injecting the container at construction time. In this example, Foo, Bar, and Baz are all types registered in the container. If a registered type in the container requires Baz's method doSomething(), Baz must first pull it's dependencies, Foo and Bar, from the container (and furthermore, their dependencies, creating a dependency tree).

```php
class Baz {

  private Foo $foo;
  private Bar $bar;

  public function __construct(IContainer $container) {
    $this->foo = $container->Foo;
    $this->bar = $container->Bar;
  }
  
  public function doSomething() : string {
    return $this->foo->myMethod();
  }
}
```

## Registering Types

Registering a type requires a symbol to identify the type when fetching it's instantiated instance from the container, and the fully qualified class name to build.

```php
/**
 * setup the type as a virtual property so that IDE's that support type checking can take advantage
 *
 * @property Foo $Foo
 */
class MyContainer extends OpenContainer {
}

$container = new MyContainer();
$container->registerType('Foo', Foo::class);

// type checks will infer this object to be an instance of Foo
$instance = $container->Foo;
```

## Registering Instances

Registering an instance requires a symbol to identify the instance when fetching from the container, and the already-created instance itself. Registering an instance is useful when the type's constructor cannot meet the requirements of an injectable class.

```php
/**
 * setup the type as a virtual property so that IDE's that support type checking can take advantage
 *
 * @property Bar $Bar
 */
class MyContainer extends OpenContainer {
}

$container = new MyContainer();
$container->registerInstance('Bar', new Bar($dependency, $outside, $of, $container));

// type checks will infer this object to be an instance of Bar
$instance = $container->Bar;
```

## Registering Builders

Registering a builder requires a symbol to identify the instance when fetching it from the container, and a closure function to execute the first time it is fetched. Registering a builder is useful if there are specialized steps that must be taken before the instance is created.

```php
/**
 * setup the type as a virtual property so that IDE's that support type checking can take advantage
 *
 * @property Qux $Qux
 */
class MyContainer extends OpenContainer {
}

$container = new MyContainer();
$container->registerBuilder('Qux', function(MyContainer $container) : Qux {

  // builder functions only have one argument, access to the container itself
  return new Qux($container, $some, $other, $dependency);
});

// type checks will infer this object to be an instance of Qux
$instance = $container->Qux;
```

## Deferred Container

A deferred container attempts to use reflection and class proxies to avoid circular dependencies. A deferred container returns proxies, which are not materialized until a method or property is accessed on the proxy. This behavior is useful as without it, every dependency in the tree is instantiated when the root dependency is first instantiated (whether those downstream dependencies will be eventually used or not). Without deferring dependency instantiation until those dependencies are actually needed, any circular dependency returns a null value or, depending on configuration, raises an _Undefined Property_ warning if they can't be resolved.

```php
$container = (new OpenContainer)->toDeferredContainer();

// all methods on a deferred container are identical to a non-deferred container
$container->registerBuilder('Plugh', function(IContainer $container) { ... });
$container->Plugh;
```
