<?php

declare(strict_types=1);

namespace BusFactor\JsonPointer;

use Stringable;

interface PointerInterface extends Stringable
{
    /**
     * @param array<string|int> $referenceTokens
     */
    public function __construct(array $referenceTokens = []);

    /**
     * @return array<string|int>
     */
    public function getReferenceTokens(): array;

    public function append(string|int $referenceToken): static;

    public function prepend(string|int $referenceToken): static;

    public function toJson(): string;

    public static function fromJson(string $json): static;
}
