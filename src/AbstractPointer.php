<?php

declare(strict_types=1);

namespace BusFactor\JsonPointer;

use InvalidArgumentException;

abstract class AbstractPointer implements PointerInterface
{
    /**
     * @var array<int|string>
     */
    private array $referenceTokens;

    /**
     * @param array<string|int> $referenceTokens
     * @throws InvalidArgumentException
     */
    public function __construct(array $referenceTokens = [])
    {
        $this->setReferenceTokens($referenceTokens);
    }

    /**
     * @return array<string|int>
     */
    public function getReferenceTokens(): array
    {
        return $this->referenceTokens;
    }

    /**
     * @param array<string|int> $referenceTokens
     * @throws InvalidArgumentException
     */
    protected function setReferenceTokens(array $referenceTokens): static
    {
        foreach ($referenceTokens as $referenceToken) {
            $type = gettype($referenceToken);

            if ($type !== 'string' && $type !== 'integer') {
                throw new InvalidArgumentException('reference token must be <string> or <int>, got <' . $type . '>');
            }
        }

        $this->referenceTokens = array_values($referenceTokens);

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromJson(string $json): static
    {
        if (preg_match('~^(/([^/])*)*$~', $json) !== 1) {
            throw new InvalidArgumentException('invalid JSON pointer format: ' . $json);
        }

        return new static(array_map(self::decodeReferenceToken(...), array_slice(explode('/', $json), 1)));
    }

    public function toJson(): string
    {
        return count($this->referenceTokens) > 0
            ? '/' . implode('/', array_map(self::encodeReferenceToken(...), $this->referenceTokens))
            : '';
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * @todo find a better way to UTF-8 encode the reference token
     */
    protected static function encodeReferenceToken(string|int $referenceToken): string|int
    {
        return is_string($referenceToken)
            ? trim(json_encode(str_replace(['~', '/'], ['~0', '~1'], $referenceToken)), '"')
            : $referenceToken;
    }

    /**
     * @todo find a better way to UTF-8 decode the reference token
     */
    protected static function decodeReferenceToken(string|int $referenceToken): string|int
    {
        return is_string($referenceToken)
            ? json_decode('{"value":"' . str_replace(['~1', '~0'], ['/', '~'], $referenceToken) . '"}')->value
            : $referenceToken;
    }
}