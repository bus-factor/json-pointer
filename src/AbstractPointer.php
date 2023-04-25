<?php

declare(strict_types=1);

namespace BusFactor\JsonPointer;

use InvalidArgumentException;
use JsonException;

use function array_map;
use function array_slice;
use function array_values;
use function count;
use function explode;
use function gettype;
use function implode;
use function is_object;
use function is_string;
use function json_decode;
use function json_encode;
use function preg_match;
use function str_replace;
use function trim;

use const JSON_THROW_ON_ERROR;

abstract class AbstractPointer implements PointerInterface
{
    /**
     * @var array<int|string>
     */
    private array $referenceTokens;

    /**
     * @param array<string|int> $referenceTokens
     *
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

    public function toJson(): string
    {
        return count($this->referenceTokens) > 0
            ? '/' . implode('/', array_map(self::encodeReferenceToken(...), $this->referenceTokens))
            : '';
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

    /**
     * @param array<string|int> $referenceTokens
     *
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
     * @throws JsonException
     *
     * @todo find a better way to UTF-8 encode the reference token
     */
    protected static function encodeReferenceToken(string|int $referenceToken): string|int
    {
        if (! is_string($referenceToken)) {
            return $referenceToken;
        }

        $value = json_encode(str_replace(['~', '/'], ['~0', '~1'], $referenceToken), flags: JSON_THROW_ON_ERROR);

        if (! is_string($value)) {
            throw new InvalidArgumentException('failed to encode JSON pointer reference token');
        }

        return trim($value, '"');
    }

    /**
     * @throws JsonException
     * @throws InvalidArgumentException
     *
     * @todo find a better way to UTF-8 decode the reference token
     */
    protected static function decodeReferenceToken(string|int $referenceToken): string|int
    {
        if (! is_string($referenceToken)) {
            return $referenceToken;
        }

        $referenceToken = str_replace(['~1', '~0'], ['/', '~'], $referenceToken);

        if (! is_string($referenceToken)) {
            throw new InvalidArgumentException('failed to decode JSON pointer reference token (1)');
        }

        $result = json_decode('{"value":"' . $referenceToken . '"}', flags: JSON_THROW_ON_ERROR);

        if (! is_object($result) || ! isset($result->value) || ! is_string($result->value)) {
            throw new InvalidArgumentException('failed to decode JSON pointer reference token (2)');
        }

        return $result->value;
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
