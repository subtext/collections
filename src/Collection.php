<?php
namespace Subtext\Collections;

use ArrayObject;
use InvalidArgumentException;
use JsonSerializable;
use Psr\Container\ContainerInterface;
use Subtext\Collections\NotFoundException;
use Throwable;

/**
 * Class Collection
 *
 * @package Subtext\Collections
 */
abstract class Collection extends ArrayObject implements JsonSerializable, ContainerInterface
{
    /**
     * @param array $data
     *
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $item) {
            $this->validate($item);
        }
        parent::__construct($data);
    }

    /**
     * Serialize the contents of the collection into an array of json objects.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $json = [];
        foreach ($this as $key => $item) {
            $interfaces = class_implements($item);
            if (isset($interfaces['JsonSerializable'])) {
                if ($this->isSequential()) {
                    $json[] = $item;
                } else {
                    $json[$key] = $item;
                }
            }
        }
        return $json;
    }

    /**
     * Override the parent class method to inject validation when values are
     * added to the collection.
     *
     * @param mixed $value
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function append(mixed $value): void
    {
        $this->validate($value);
        parent::append($value);
    }

    /**
     * Override the parent class method to inject validation when values are
     * * added to the collection.
     *
     * @param mixed $key
     * @param mixed $value
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        $this->validate($value);
        parent::offsetSet($key, $value);
    }

    /**
     * Absorb the values of another collection, into the current collection.
     * Overwrites values with the same key.
     *
     * @param Collection $other
     *
     * @return void
     */
    public function absorb(Collection $other): void
    {
        foreach ($other as $key => $value) {
            if (array_is_list($this->getArrayCopy())) {
                $this->append($value);
            } else {
                $this->set($key, $value);
            }
        }
    }

    /**
     * Determines if the container can return an entry for the given key.
     *
     * @param string $id id is used for compatibility, key is used elsewhere
     *
     * @return bool
     */
    public function has(string $id): bool
    {
        return parent::offsetExists($id);
    }

    /**
     * Finds an entry in the container by its key and returns it.
     *
     * @param string $id id is used for compatibility, key is used elsewhere
     *
     * @return mixed|null
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function get(string $id)
    {
        try {
            if ($this->has($id)) {
                $value = parent::offsetGet($id);
            } else {
                throw new NotFoundException(
                    'Not value could be found for key: ' . $id
                );
            }
        } catch (NotFoundException $e) {
            throw $e;
            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            throw new ContainerException(
                'An error occurred while trying to retrieve the value',
                $e->getCode(),
                $e
            );
            // @codeCoverageIgnoreEnd
        }

        return $value;
    }

    /**
     * Add a value to the collection with a string key reference.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        parent::offsetSet($key, $value);
    }

    /**
     * Remove an item from the collection by its key.
     *
     * @param string $key
     *
     * @return void
     */
    public function unset(string $key): void
    {
        parent::offsetUnset($key);
    }

    /**
     * Returns the key values for the collection if the collection is not
     * sequential.
     *
     * @return array
     */
    public function getKeys(): array
    {
        return array_keys($this->getArrayCopy());
    }

    /**
     * Retrieve the first value of the collection.
     *
     * @return mixed
     */
    public function getFirst(): mixed
    {
        $iterator = $this->getIterator();
        $iterator->rewind();
        return $iterator->current();
    }

    /**
     * Returns the Nth value in this collection (1-based index).
     *
     * @param int $n The position of the item to retrieve
     *
     * @return mixed The value at the specified position, or null, if the
     *               position specified does not exist.
     *
     * @example
     * $collection = new Collection(['a', 'b', 'c']);
     * $collection->getNth(1); // returns 'a'
     * $collection->getNth(3); // returns 'c'
     * $collection->getNth(5); // returns null
     */
    public function getNth(int $n): mixed
    {
        $iterator = $this->getIterator();
        if ($n > $this->count()) {
            $nth = null;
        } else {
            $iterator->rewind();
            for ($i = 1; $i < $n; $i++) {
                $iterator->next();
            }
            $nth = $iterator->current();
        }
        return $nth;
    }


    /**
     * Retrieve the last value of the collection
     *
     * @return mixed|null
     */
    public function getLast(): mixed
    {
        return $this->getNth($this->count());
    }

    /**
     * Returns true if the array has only numeric keys in order, otherwise
     * returns false.
     *
     * @return bool
     */
    public function isSequential(): bool
    {
        return array_is_list($this->getArrayCopy());
    }

    /**
     * Returns true if the collection has no items.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Empty the collection of all contents.
     *
     * @return void
     */
    public function empty(): void
    {
        $this->exchangeArray([]);
    }

    /**
     * Assigns the value to a new key name in the collection.
     *
     * @param string $oldKey
     * @param string $newKey
     *
     * @return void
     */
    public function renameKey(string $oldKey, string $newKey): void
    {
        if ($this->has($oldKey)) {
            $value = $this->get($oldKey);
            $this->unset($oldKey);
            $this->set($newKey, $value);
        }
    }

    /**
     * Returns a subset of the collection, similar to array_slice().
     *
     * @param int      $offset Starting position in the collection. If negative, counts from the end.
     * @param int|null $length Number of items to return.
     *                          - Positive: returns up to that many items.
     *                          - Negative: excludes that many items from the end.
     *                          - Null: returns all items from offset to the end.
     *
     * @return Collection A new collection containing the selected slice.
     */
    public function slice(int $offset, ?int $length = null): self
    {
        return new static(array_slice($this->getArrayCopy(), $offset, $length));
    }

    /**
     * Returns an array of collections, each of which contains $size values.
     *
     * @param int $size
     *
     * @return Collection[]
     */
    public function chunk(int $size): array
    {
        $chunks  = [];
        foreach (array_chunk($this->getArrayCopy(), $size) as $chunk) {
            array_push($chunks, new static($chunk));
        }
        return $chunks;
    }

    /**
     * Returns a collection of items filtered by the callback.
     *
     * @param callable $callback
     * @return Collection
     */
    public function filter(callable $callback): self
    {
        return new static(array_filter($this->getArrayCopy(), $callback));
    }

    /**
     * Returns a collection containing the results of applying the callback to
     * the corresponding items in this collection.
     *
     * @param callable $callback
     *
     * @return Collection
     */
    public function map(callable $callback): self
    {
        return new static(array_map($callback, $this->getArrayCopy()));
    }

    /**
     * Applies the user supplied callback function to each item of the collection.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function walk(callable $callback ): void
    {
        $copy = $this->getArrayCopy();
        array_walk($copy, $callback);
        $this->exchangeArray($copy);
    }

    /**
     * Returns a single value based on the callback. An initial value may be
     * supplied. Each iteration of the callback should return a single value.
     *
     * @param callable $callback
     * @param mixed|null $initial
     *
     * @return mixed
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->getArrayCopy(), $callback, $initial);
    }

    /**
     * Validates the contents of the collection. Promotes strict typing, and
     * allows strongly typed fluent interfaces. Should throw an invalid
     * argument exception if the value is not valid.
     *
     * @param mixed $value
     *
     * @return void
     * @throws InvalidArgumentException
     */
    abstract protected function validate(mixed $value): void;
}
