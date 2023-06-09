# JSON Pointer

This library provides an implementation of RFC-6901 ([JavaScript Object Notation (JSON) Pointer](https://datatracker.ietf.org/doc/html/rfc6901)).

## Usage

```php
namespace BusFactor\JsonPointer;

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

    public static function fromJson(string $json): static;

    public function toJson(): string;
}
```

### Mutable pointer

```php
$pointer = new \BusFactor\JsonPointer\Pointer(['data', 'users', '1']);
```

### Immutable pointer

```php
$immutablePointer = new \BusFactor\JsonPointer\ImmutablePointer(['data', 'users', '1']);
```
