<?php

declare(strict_types=1);

namespace BusFactor\JsonPointerTest;

use BusFactor\JsonPointer\Pointer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BusFactor\JsonPointer\AbstractPointer
 * @covers \BusFactor\JsonPointer\Pointer
 */
class PointerTest extends TestCase
{
    public function testConstruct(): void
    {
        $pointer = new Pointer(['foo', 0, 'bar']);

        self::assertSame(['foo', 0, 'bar'], $pointer->getReferenceTokens());
    }

    /**
     * @testWith [[true], "boolean"]
     *           [[false], "boolean"]
     *           [[null], "NULL"]
     *           [[[]], "array"]
     *           [[1.1], "double"]
     */
    public function testConstructThrowsExceptionOnInvalidReferenceToken(mixed $referenceTokens, string $type): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('reference token must be <string> or <int>, got <' . $type . '>');

        // @phpstan-ignore-next-line
        new Pointer($referenceTokens);
    }

    /**
     * @param array<int|string> $expectedReferenceTokens
     * @testWith ["/foo/0/bar", ["foo", "0", "bar"]]
     *           ["", []]
     *           ["/", [""]]
     *           ["//", ["", ""]]
     *           ["/~0", ["~"]]
     *           ["/~1", ["/"]]
     *           ["/~01", ["~1"]]
     *           ["/~00", ["~0"]]
     *           ["/\\u00e4\\u00f6\\u00fc~0", ["äöü~"]]
     */
    public function testFromJson(string $json, array $expectedReferenceTokens): void
    {
        $pointer = Pointer::fromJson($json);

        self::assertSame($expectedReferenceTokens, $pointer->getReferenceTokens());
    }

    public function testFromJsonThrowsExceptionOnInvalidJsonPointer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid JSON pointer format: foo/0/bar/');

        Pointer::fromJson('foo/0/bar/');
    }

    /**
     * @param array<int|string> $referenceTokens
     * @testWith [["foo", 0, "bar"], "/foo/0/bar"]
     *           [[""], "/"]
     *           [["", ""], "//"]
     *           [["~"], "/~0"]
     *           [["/"], "/~1"]
     *           [["~1"], "/~01"]
     *           [["~0"], "/~00"]
     *           [["äöü~"], "/\\u00e4\\u00f6\\u00fc~0"]
     */
    public function testToJson(array $referenceTokens, string $expectedJson): void
    {
        $pointer = new Pointer($referenceTokens);

        self::assertSame($expectedJson, $pointer->toJson());
    }

    public function testAppend(): void
    {
        $pointer = new Pointer(['foo', 0, 'bar']);
        $otherPointer = $pointer->append('baz');

        self::assertSame(['foo', 0, 'bar', 'baz'], $pointer->getReferenceTokens());
        self::assertSame(['foo', 0, 'bar', 'baz'], $otherPointer->getReferenceTokens());
        self::assertSame($pointer, $otherPointer);
    }

    public function testPrepend(): void
    {
        $pointer = new Pointer(['foo', 0, 'bar']);
        $otherPointer = $pointer->prepend('baz');

        self::assertSame(['baz', 'foo', 0, 'bar'], $pointer->getReferenceTokens());
        self::assertSame(['baz', 'foo', 0, 'bar'], $otherPointer->getReferenceTokens());
        self::assertSame($pointer, $otherPointer);
    }

    /**
     * @param array<int|string> $referenceTokens
     * @testWith [["foo", 0, "bar"], "/foo/0/bar"]
     *           [[""], "/"]
     *           [["", ""], "//"]
     *           [["~"], "/~0"]
     *           [["/"], "/~1"]
     *           [["~1"], "/~01"]
     *           [["~0"], "/~00"]
     *           [["äöü~"], "/\\u00e4\\u00f6\\u00fc~0"]
     */
    public function testToString(array $referenceTokens, string $expectedString): void
    {
        $pointer = new Pointer($referenceTokens);

        self::assertSame($expectedString, (string) $pointer);
    }
}
