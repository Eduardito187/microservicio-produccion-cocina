<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Application\Integration\Events\Support;

use InvalidArgumentException;

/**
 * @class Payload
 * @package App\Application\Integration\Events\Support
 */
class Payload
{
    /**
     * @var array
     */
    private $payload;

    /**
     * Constructor
     *
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @param array $keys
     * @param string|null $default
     * @param bool $required
     * @return string|null
     */
    public function getString(array $keys, ?string $default = null, bool $required = false): ?string
    {
        $value = $this->getValue($keys);

        if ($value === null || $value === '') {
            if ($required) {
                throw new InvalidArgumentException('Missing required field: ' . implode('|', $keys));
            }
            return $default;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (!is_string($value)) {
            if ($required) {
                throw new InvalidArgumentException('Invalid field type: ' . implode('|', $keys));
            }
            return $default;
        }

        return $value;
    }

    /**
     * @param array $keys
     * @param array|null $default
     * @return array|null
     */
    public function getArray(array $keys, ?array $default = null): ?array
    {
        $value = $this->getValue($keys);
        return is_array($value) ? $value : $default;
    }

    /**
     * @param array $keys
     * @param int|null $default
     * @return int|null
     */
    public function getInt(array $keys, ?int $default = null): ?int
    {
        $value = $this->getValue($keys);
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }
        return $default;
    }

    /**
     * @param array $keys
     * @return mixed
     */
    private function getValue(array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->payload)) {
                return $this->payload[$key];
            }
        }

        return null;
    }
}
