OpenContainer
=============
A dependency injection container for PHP, please enjoy responsibly.

Requirements
------------
* PHP 5.3+

Adding OpenContainer to your application
----------------------------------------
Simply instantiate OpenContainer and you're ready to go.
```php
$Container = new OpenContainer();
```

Injectable Class
----------------
An injectable class uses constructor injection in a way that the container can instantiate it on the fly when it is required as dependency. In this example, Foo, Bar, and Baz are all in the container. If a registered type in the container requires Baz's method doSomething(), Baz must first go into the container and get it's dependencies, Foo and Bar.
```php
class Baz {

  private $Foo;
  private $Bar;

  public function __construct(OpenContainer $Container) {
    $this->Foo = $Container->Foo;
    $this->Bar = $Container->Bar;
  }
  
  public function doSomething() {
    return $this->Foo->myMethod();
  }
}
```

Registering Types
-----------------
There are three different types to register in the container.

* Stateless
    * The instance is created when another registered type depends on it, then thrown away. This is useful for stateless services or business logic that only depend on other types registered in the container.
* Shared State
    * The instance is created when another registered type depends on it, and remains in the container. All requests to this type will receive a reference to the same instance.
* Instance
    * The instance is created beforehand, then registered in the container. The container will not attempt to create this type, and will return the registered instance when other dependencies request this type.

### Registering Stateless Type
Register a stateless type with a type name and a fully qualified injectable class name OR constructor callback function. These types are recreated every time their are requested.
```php
$Container->registerType('Foo', 'Path\To\Foo');
    
$Container->registerType('Foo', function(OpenContainer $Container) {
  $Factory = new FooFactory();
  return $Factory->newFoo();
});
```
### Registering Shared State Type
Register a shared state type with a type name and a fully qualified injectable class name OR constructor callback function. These types will be constructed once.
```php
$Container->registerSharedType('Bar', 'Path\To\Bar');

$Container->registerSharedType('Bar', function(OpenContainer $Container) {
  return new Bar();
});
```
### Registering Instance
Register an instance with a type name and the instance. This instance will be returned whenever the type is requested.
```php
$Baz = Baz::newBaz($arg1, $arg2);
$Container->registerInstance('Baz', $Baz);
```
