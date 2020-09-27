Symfony Service Definitions via Annotations
==

When using Symfonys autowire feature, a lot of stuff (e.g service aliases / service tags) must be configured in the
services.yaml which makes the autowire feature a bit useless then.

This bundle tries to prevent the developer from having to enter the services.yaml and configure services there.


##Important
Bundle and readme is currently under construction but should work anyways.

* [ ] Write better documentation
* [ ] Add tests
* [ ] Add decoration support 

##Examples

#### Example 1: Tagged services with an interfaces


When annotate an interface with `@ServiceTag` all classes which implements the interface will be registered as a tagged service.
This logic only works with interfaces and abstract classes.

When you want to tag a single class instead a interface or abstract class, simply use the annotation the same way as in this example.
```php
<?php

namespace App\Serializer;

use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTag;

/**
 * @ServiceTag(CircularReferenceHandlerInterface::SERVICE_TAG, priority=-222)
 */
interface CircularReferenceHandlerInterface
{
    public const SERVICE_TAG = 'sm.serializer.circular_reference_handler';

    public function supports(object $object): bool;

    public function handle(object $object);
}
```



#### Example 2: Service tag attributes definition


When using the interface/abstract service tagging (see Example 1) you can overwrite tag arguments in your child class 
by using `@ServiceTagArgument`.

You also can use this annotation in a normal class to add arguments to any tag

When you define more than 1 annotation of type `@ServiceTag` in your class or your parent class/interface you must 
specify for which tag your annotation `@ServiceTagArgument` is responsible.

e.g. `@ServiceTagArgument(tag="my_tag", argument="priority", value=-23)`

```php
<?php

namespace App\Serializer;

use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTagArgument;

/**
 * @ServiceTagArgument(argument="priority", value=-9999, ignoreWhenDefined=false)
 * @ServiceTagArgument(argument="someOther", value=false)
 */
class DefaultCircularReferenceHandler implements CircularReferenceHandlerInterface
{
    public function supports(object $object): bool
    {
        return true;
    }

    public function handle(object $object)
    {
        dd($object);
    }
}
```


#### Example 3: Service Alias

By using the annotation `@ServiceAlias` you can specify an alias for you service class.

```php
<?php


namespace App\Serializer;

use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceAlias;

/**
 * @ServiceAlias(CircularReferenceHandlerInterface::class)
 */
class CircularReferenceHandlerChain implements CircularReferenceHandlerInterface
{
    private array $handlers = [];

    public function __construct(iterable $handlers = [])
    {
        $this->handlers = $handlers;
    }

    public function handle(object $object)
    {

    }

    public function supports(object $object): bool
    {
    }
}
```

### Example 4: Prevent loading classes as service (e.g. when using autowire)
Sometimes you dont want that your class is loaded into die dic, for that you can use the `@NoService` annotation.
Every class which is annotated with this annotation would be removed from the symfony container before container build is finished.

```php
<?php

namespace App\Struct;

use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\NoService;

/**
 * @NoService()
 */
class SomeStruct
{
    public ?string $name = null;
}
```

#### Example 5: Ignore annotation from parent classes

In default annotation from parent classes (as long as they are abstract or interfaces) will be used for your child class to.
To prevent unwanted service configurations you can use the `@IgnoreParentServiceAnnotations` annotation.

```php
<?php

namespace App\Serializer;

use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\IgnoreParentServiceAnnotations;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTag;
use SimonMarx\Symfony\Bundles\ServiceAnnotations\Annotation\ServiceTagArgument;

/** 
 * @ServiceTag("some_service_tag")
 * @ServiceTagArgument(argument="priority", value=2)
 */
abstract class MyParent {}

## Ignores all tags (related to service configuration) in your parent
/**
 * @IgnoreParentServiceAnnotations()
 */
class MyChild extends MyParent {}

## ignores only the configures annotations
/**
 * @IgnoreParentServiceAnnotations({ServiceTagArgument::class})
 */
class AnotherChild extends MyParent {}


## ignores all annotations except the excluded
/**
 * @IgnoreParentServiceAnnotations(exclude={ServiceTag::class})
 */
class SomeChild extends MyParent {}

```