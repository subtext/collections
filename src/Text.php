<?php

namespace Subtext\Collections;

use Subtext\Collections;

class Text extends Collections\Collection
{
    public function concat(string $glue = ', '): string
    {
        return implode($glue, $this->getArrayCopy());
    }

    protected function validate(mixed $value): void
    {
        if ((!is_string($value)) || empty($value)) {
            throw new \InvalidArgumentException(
                'Value must be a non-empty string.'
            );
        }
    }
}
