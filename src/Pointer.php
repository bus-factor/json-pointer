<?php

declare(strict_types=1);

namespace BusFactor\JsonPointer;

class Pointer extends AbstractPointer
{
    public function append(string|int $referenceToken): static
    {
        return $this->setReferenceTokens([...$this->getReferenceTokens(), $referenceToken]);
    }

    public function prepend(string|int $referenceToken): static
    {
        return $this->setReferenceTokens([$referenceToken, ...$this->getReferenceTokens()]);
    }
}
