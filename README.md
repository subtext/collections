# Subtext\Collections
![Run Unit Tests](https://github.com/subtext/collections/actions/workflows/tests-unit.yml/badge.svg)

## Abstract base class that provides a Java-style Collection interface in PHP.
This class brings structured, type-safe, and object-oriented collection handling
to PHP, inspired by Java's `Collection` framework. It extends `ArrayObject`
and implements `JsonSerializable` and `Psr\Container\ContainerInterface` to offer
modern PHP compatibility along with predictable, validated container behavior.

Subclasses must implement the protected `validate($value): void` method to define
what values the collection accepts. Rejected values should throw an instance of
InvalidArgumentException.

### Key Features

- Enforces validation on all insertions via `::validate(mixed $value)`
- Supports both sequential (list-style) and associative (map-style) arrays
- Implements `ContainerInterface` for ease of access and consistency
- Implements `JsonSerializable` for structured serialization of contents
- Offers utility methods similar to Java/JavaScript/TypeScript:
  - getFirst  - get the first element in a collection
  - getLast   - get the last element in a collection
  - getNth    - get the nth element in a collection, index begins at 1
  - getKeys   - get the keys for a map style collection
  - slice     - get a new collection consisting of a slice of the current one
  - chunk     - create multiple collections of a specific chunk size
  - filter    - pass a callable to filter the elements of a collection
  - map       - pass a callable to map the elements of a collection to a new collection
  - walk      - use a callable to apply transformation to every member of a collection
  - reduce    - reduce the elements of a collection to a single value
  - empty     - empty the collection of all values
  - absorb    - absorb the contents of a collection into another
  - renameKey - rename the key for a specified value in the collection

### Example

```php
namespace Foo\Bars;

use Foo\Bar;
use InvalidArgumentException;
use Subtext\Collections;

class Collection extends Collections\Collection
{
    protected function validate(mixed $value): void
    {
        if (!($value instanceof Bar)) {
            throw new InvalidArgumentException(sprintf(
                'Value added to collection must be an instance of %s',
                Bar::class
            ));
        }
    }
}
```
