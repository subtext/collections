<?php

namespace Subtext\NotFoundException;

use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;

class NotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{

}
