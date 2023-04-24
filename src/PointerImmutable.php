<?php

declare(strict_types=1);

namespace BusFactor\JsonPointer;

class PointerImmutable extends AbstractPointer
{
    public function append(string|int $referenceToken): static
    {
        return new static([...$this->getReferenceTokens(), $referenceToken]);
    }

    public function prepend(string|int $referenceToken): static
    {
        return new static([$referenceToken, ...$this->getReferenceTokens()]);
    }
}
