<?php

declare(strict_types=1);

namespace BusFactor\JsonPointerTest;

use BusFactor\JsonPointer\PointerImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \BusFactor\JsonPointer\AbstractPointer
 * @covers \BusFactor\JsonPointer\PointerImmutable
 */
class PointerImmutableTest extends TestCase
{
    public function testConstruct(): void
    {
        $pointer = new PointerImmutable(['foo', 0, 'bar']);

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

        new PointerImmutable($referenceTokens);
    }

    /**
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
        $pointer = PointerImmutable::fromJson($json);

        self::assertSame($expectedReferenceTokens, $pointer->getReferenceTokens());
    }

    public function testFromJsonThrowsExceptionOnInvalidJsonPointer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid JSON pointer format: foo/0/bar/');

        PointerImmutable::fromJson('foo/0/bar/');
    }

    /**
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
        $pointer = new PointerImmutable($referenceTokens);

        self::assertSame($expectedJson, $pointer->toJson());
    }

    public function testAppend(): void
    {
        $pointer = new PointerImmutable(['foo', 0, 'bar']);
        $otherPointer = $pointer->append('baz');

        self::assertSame(['foo', 0, 'bar'], $pointer->getReferenceTokens());
        self::assertSame(['foo', 0, 'bar', 'baz'], $otherPointer->getReferenceTokens());
    }

    public function testPrepend(): void
    {
        $pointer = new PointerImmutable(['foo', 0, 'bar']);
        $otherPointer = $pointer->prepend('baz');

        self::assertSame(['foo', 0, 'bar'], $pointer->getReferenceTokens());
        self::assertSame(['baz', 'foo', 0, 'bar'], $otherPointer->getReferenceTokens());
    }

    /**
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
        $pointer = new PointerImmutable($referenceTokens);

        self::assertSame($expectedString, (string) $pointer);
    }
}
