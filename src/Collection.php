<?php
namespace Subtext\Collections;

use ArrayObject;

/**
 * Class Collection
 *
 * @package Subtext\Collections
 */
class Collection extends ArrayObject
{
    protected $manifest = [];

    public function __construct($input = [], $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, $flags, $iterator_class);
    }
}