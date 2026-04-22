<?php

/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Infrastructure\Tracing;

/**
 * W3C Trace Context representation.
 *
 * traceparent header format:  00-{trace_id:32}-{span_id:16}-{flags:2}
 * See: https://www.w3.org/TR/trace-context/
 */
final class TraceContext
{
    public const VERSION = '00';
    public const FLAG_SAMPLED = '01';
    public const FLAG_NOT_SAMPLED = '00';

    public function __construct(
        public readonly string $traceId,
        public readonly string $spanId,
        public readonly bool $sampled = true,
    ) {}

    public static function generate(bool $sampled = true): self
    {
        return new self(self::randomHex(32), self::randomHex(16), $sampled);
    }

    public static function fromTraceparent(?string $header): ?self
    {
        if (! is_string($header) || $header === '') {
            return null;
        }
        $parts = explode('-', trim($header));
        if (count($parts) !== 4) {
            return null;
        }
        [$version, $traceId, $spanId, $flags] = $parts;
        if ($version !== self::VERSION) {
            return null;
        }
        if (! self::isHex($traceId, 32) || ! self::isHex($spanId, 16) || ! self::isHex($flags, 2)) {
            return null;
        }
        if ($traceId === str_repeat('0', 32) || $spanId === str_repeat('0', 16)) {
            return null;
        }

        return new self($traceId, $spanId, (hexdec($flags) & 0x01) === 0x01);
    }

    public function toTraceparent(): string
    {
        return sprintf(
            '%s-%s-%s-%s',
            self::VERSION,
            $this->traceId,
            $this->spanId,
            $this->sampled ? self::FLAG_SAMPLED : self::FLAG_NOT_SAMPLED
        );
    }

    public function child(): self
    {
        return new self($this->traceId, self::randomHex(16), $this->sampled);
    }

    private static function randomHex(int $length): string
    {
        return bin2hex(random_bytes(intdiv($length, 2)));
    }

    private static function isHex(string $value, int $expectedLength): bool
    {
        return strlen($value) === $expectedLength && ctype_xdigit($value);
    }
}
