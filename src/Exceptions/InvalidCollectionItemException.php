<?php

namespace Subtext\Collections\Exceptions;


use Throwable;

class InvalidCollectionItemException extends \InvalidArgumentException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}