<?php
namespace Subtext\Collections;

use ArrayObject;
use Psr\Container\ContainerInterface;
use Subtext\Collections\Exceptions\InvalidCollectionItemException;

/**
 * Class Collection
 *
 * @package Subtext\Collections
 */
abstract class Collection extends ArrayObject
{
    protected $type = 'NULL';

    protected $namespace = null;

    public function __construct($input = [], $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, $flags, $iterator_class);
        $this->validateCollection();
    }

    public function append($value)
    {
        if (!$this->validateType($value)) {
            throw new InvalidCollectionItemException(
                "The appended item is not valid for type: {$this->type}", 500
            );
        }
        parent::append($value);
    }

    public function offsetSet($index, $newval)
    {
        if (!$this->validateType($newval)) {
            throw new InvalidCollectionItemException(
                "The item of key: {$index} is not valid for type: {$this->type}", 500
            );
        }
        parent::offsetSet($index, $newval);
    }

//    abstract protected function validate(): bool;

    /**
     * Validate all existing memebers of the collection
     */
    private function validateCollection()
    {
        $loop = $this->getIterator();
        $loop->rewind();
        while ($loop->valid()) {
            if (!$this->validateType($loop->current())) {
                throw new InvalidCollectionItemException(
                    "The item of key: {$loop->key()} is not valid for type: {$this->type}", 500
                );
            }
            $loop->next();
        }
    }

    private function validateType($var): bool
    {
        $validity = false;
        switch ($this->type) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'string':
            case 'resource':
                $validity = (gettype($var) === $this->type);
                break;
            case 'array':
                $validity = is_array($var);
                break;
            case 'object':
                if (method_exists($var, 'validate')) {
                    if (!empty($this->namespace)) {
                        if (get_class($var) === $this->namespace) {
                            $validity = $var->validate();// the namespace is valid
                        }
                    } else {
                        $validity = $var->validate();
                    }
                }
                break;
            default: // unknown type
                break;
        }

        return $validity;
    }
}