<?php

namespace Subtext\Collections;

use InvalidArgumentException;
use Subtext\Collections;

class Number extends Collections\Collection
{
    public function getIntegers(): self
    {
        return $this->map(function ($value) {
            if (strpos(strval($value), '.') !== false) {
                $value = explode('.', $value)[0];
            }
            return intval($value);
        });
    }

    public function getFloats(): self
    {
        return $this->map(function ($value) {
            return floatval($value);
        });
    }

    protected function validate(mixed $value): void
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException(
                'Value must be numeric'
            );
        }
    }
}
