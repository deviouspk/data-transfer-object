# Data transfer objects with batteries included

[![Latest Stable Version](https://poser.pugx.org/larapie/data-transfer-object/v/stable)](https://packagist.org/packages/larapie/data-transfer-object)
[![Build Status](https://travis-ci.org/larapie/data-transfer-object.svg?branch=master)](https://travis-ci.org/larapie/data-transfer-object)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/larapie/data-transfer-object/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/larapie/data-transfer-object/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/larapie/data-transfer-object/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/larapie/data-transfer-object/?branch=master)
[![StyleCI](https://github.styleci.io/repos/177636567/shield?branch=master)](https://github.styleci.io/repos/177636567)
[![Total Downloads](https://poser.pugx.org/larapie/data-transfer-object/downloads)](https://packagist.org/packages/larapie/data-transfer-object)

## Note
The base repo is originally developed and maintained by spatie (https://github.com/spatie/data-transfer-object). Our goal is to improve this this package with additional features.

## Installation

You can install the package via composer:

```bash
composer require larapie/data-transfer-object
```

## Have you ever…

… worked with an array of data, retrieved from a request, a CSV file or a JSON API; and wondered what was in it?

Here's an example:

```php
public function handleRequest(array $dataFromRequest)
{
    $dataFromRequest[/* what to do now?? */];
}
```

The goal of this package is to structure "unstructured data", which is normally stored in associative arrays.
By structuring this data into an object, we gain several advantages:

- We're able to type hint data transfer objects, instead of just calling them `array`.
- By making all properties on our objects typeable, we're sure that their values are never something we didn't expect.
- Because of typed properties, we can statically analyze them and have auto completion.

Let's look at the example of a JSON API call:

```php
$post = $api->get('posts', 1); 

[
    'title' => '…',
    'body' => '…',
    'author_id' => '…',
]
```

Working with this array is difficult, as we'll always have to refer to the documentation to know what's exactly in it. 
This package allows you to create data transfer object definitions, classes, which will represent the data in a structured way.

We did our best to keep the syntax and overhead as little as possible:

```php
class PostData extends DataTransferObject
{
    /** @var string */
    public $title;
    
    /** @var string */
    public $body;
    
    /** @var \Author */
    public $author;
}
```

An object of `PostData` can from now on be constructed like so:

```php
$postData = new PostData([
    'title' => '…',
    'body' => '…',
    'author_id' => '…',
]);
```

Now you can use this data in a structured way:

```php
$postData->title;
$postData->body;
$postData->author_id;
```

It's, of course, possible to add static constructors to `PostData`:

```php
class PostData extends DataTransferObject
{
    // …
    
    public static function fromRequest(Request $request): self
    {
        return new self([
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'author' => Author::find($request->get('author_id')),
        ]);
    }
}
```

By adding doc blocks to our properties, their values will be validated against the given type; 
and a `TypeError` will be thrown if the value doesn't comply with the given type.

Here are the possible ways of declaring types:

```php
class PostData extends DataTransferObject
{
    /**
     * Built in types: 
     *
     * @var string 
     */
    public $property;
    
    /**
     * Classes with their FQCN: 
     *
     * @var \App\Models\Author
     */
    public $property;
    
    /**
     * Lists of types: 
     *
     * @var \App\Models\Author[]
     */
    public $property;
    
    /**
     * Union types: 
     *
     * @var string|int
     */
    public $property;
    
    /**
     * Nullable types: 
     *
     * @var string|null
     */
    public $property;
    
    /**
     * Mixed types: 
     *
     * @var mixed|null
     */
    public $property;
    
    /**
     * No type, which allows everything
     */
    public $property;
}
```
When PHP 7.4 introduces typed properties, you'll be able to simply remove the doc blocks and type the properties with the new, built-in syntax.

### Optional Properties

By default all dto properties are required. If you want to make certain properties on the dto optional:

```php
class PostData extends DataTransferObject
{
    /**
     * @Optional
     * @var string $name
     */
    public $name;
}
```

###### Note
The default value will NOT be set when a property is annotated as optional!

### Additional Properties

By default only dto properties can be set on the dto. Attempting to input data that is not declared as a public property on the dto will throw a `UnknownPropertiesDtoException`.
If you want to allow additional properties you can do so by implementing the `AdditionalProperties` or `WithAdditionalProperties` interface.

AdditionalProperties:

```php
class PostData extends DataTransferObject implements AdditionalProperties
{
    /**
     * @var string $name
     */
    public $name;
}

$dto = new PostData(["name" => "foo", "address" => "bar"]);
$dto->toArray();

returns:
["name" => "foo"]
```

WithAdditionalProperties:

```php
class PostData extends DataTransferObject implements WithAdditionalProperties
{
    /**
     * @var string $name
     */
    public $name;
}

$dto = new PostData(["name" => "foo", "address" => "bar"]);
$dto->toArray();

returns:
["name" => "foo", "address" => "bar"]
```

### Overriding & Adding Properties

If you want to add or override a certain value on the dto you can do it as follows:

Adding Data:
```php
    public function create(PostData $data, User $user)
    {
        $data->with('user_id', $user->id);
        return $this->repository->create($data->toArray());
    }
```

Overriding Property:

```php
    public function create(PostData $data, User $user)
    {
        if($this->user->isAdmin()){
            $data->override('name', 'admin');
        }
        $data->with('user_id', $user->id);
        return $this->repository->create($data->toArray());
    }
```

##### Notes:
- You cannot add or override data on an immutable dto. You also can't override immutable properties.

- You cannot use the with method to add properties that are declared as public properties on the dto.

### Validation

##### Constraints
If you want to validate the input of a property. You can do so with annotations through symfony constraints.
```php
class PostData extends DataTransferObject
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(min = 3, max = 20)
     */
    public $name;
}
```

##### Constraint Inheritence
If you want to extend a dto and add extra constraints or the optional annotation you can do so by adding the `Inherit` annotation.
This will merge all existing constraints from the parent class. If no type is specified on the current class it will also inherit the type of the parent dto.
```php
class UpdatePostData extends PostData
{
    /**
     * @Optional
     * @Inherit
     */
    public $name;
}
```

###### Notes
- The `Optional` annotation will not be inherited from the parent class. This is to ensure you always have a clear overview of what values are required in a dto.
- Validation is done upon accessing variables through the magic __get method `$dto->property` or when outputting the values of the array through the `toArray()` or `all()` methods. You can also call the `validat()` method manually. If the dto is not valid it will throw a `ValidatorException`.

To implement this functionality the excellent `symfony\validation` library was used. 
For more info please checkout https://symfony.com/doc/current/validation.html


### Working with collections

If you're working with collections of DTOs, you probably want auto completion and proper type validation on your collections too.
This package adds a simple collection implementation, which you can extend from.

```php
use \Spatie\DataTransferObject\DataTransferObjectCollection;

class PostCollection extends DataTransferObjectCollection
{
    public function current(): PostData
    {
        return parent::current();
    }
}
```

By overriding the `current` method, you'll get auto completion in your IDE, 
and use the collections like so.

```php
foreach ($postCollection as $postData) {
    $postData-> // … your IDE will provide autocompletion.
}

$postCollection[0]-> // … and also here.
```

Of course you're free to implement your own static constructors:

```php
class PostCollection extends DataTransferObjectCollection
{
    public static function create(array $data): PostCollection
    {
        $collection = [];

        foreach ($data as $item)
        {
            $collection[] = PostData::create($item);
        }

        return new self($collection);
    }
}
```

### Automatic casting of nested DTOs

If you've got nested DTO fields, data passed to the parent DTO will automatically be casted.

```php
class PostData extends DataTransferObject
{
    /** @var \AuthorData */
    public $author;
}
```

`PostData` can now be constructed like so:

```php
$postData = new PostData([
    'author' => [
        'name' => 'Foo',
    ],
]);
```

### Automatic casting of nested array DTOs

Similarly to above, nested array DTOs will automatically be casted.

```php
class TagData extends DataTransferObject
{
    /** @var string */
   public $name;
}

class PostData extends DataTransferObject
{
    /** @var \TagData[] */
   public $tags;
}
```

`PostData` will automatically construct tags like such:

```php
$postData = new PostData([
    'tags' => [
        ['name' => 'foo'],
        ['name' => 'bar']
    ]
]);
```
**Attention**: For nested type casting to work your Docblock definition needs to be a Fully Qualified Class Name (`\App\DTOs\TagData[]` instead of `TagData[]` and an use statement at the top)

### Immutability

If you want your data object to be never changeable (this is a good idea in some cases), you can make it immutable:

```php
class PostData extends DataTransferObject
{
    use MakeImmutable;
    
    /** @var string */
    public $name;
}
```


If you only want to make a certain property immutable you can annotate this on the variable.

```php
class PostData extends DataTransferObject
{
    /**
     * @Immutable
     * @var string $name
     */
    public $name;
}
```

Trying to change a property of `$postData` after it's constructed, will result in a `ImmutableDtoException`.

### Helper functions

There are also some helper functions provided for working with multiple properties at once. 

```php
$postData->all();

$postData
    ->only('title', 'body')
    ->toArray();
    
$postData
    ->except('author')
    ->toArray();
``` 

You can also chain these methods:

```php
$postData
    ->except('title')
    ->except('body')
    ->toArray();
```

It's important to note that `except` and `only` are immutable, they won't change the original data transfer object.

### Exception handling

Beside property type validation, you can also be certain that the data transfer object in its whole is always valid.
On outputting the data from a data transfer object (through the `all()` & `toArray()` methods and also when you access the properties of the dto e.g. `$dto->name`) , we'll validate whether all required properties are set, if the constraints are met and if each property is of the correct type. 
If not, a `Larapie\DataTransferObject\Exceptions\ValidatorException` will be thrown.

Likewise, if you're trying to set non-defined properties, you'll get a `Larapie\DataTransferObject\Exceptions\UnknownPropertiesDtoException`.

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Brent Roose](https://github.com/brendt)
- [Anthony Vancauwenberghe](https://github.com/larapie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
