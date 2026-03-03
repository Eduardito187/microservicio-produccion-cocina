<?php
/**
 * Microservicio "Produccion y Cocina"
 */

namespace App\Domain\Produccion\ValueObjects;

use App\Domain\Shared\ValueObjects\ValueObject;
use DomainException;

/**
 * @class PackageStatus
 * @package App\Domain\Produccion\ValueObjects
 */
class PackageStatus extends ValueObject
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        if ($normalized === '') {
            throw new DomainException('PackageStatus cannot be empty');
        }

        $allowed = ['pendiente', 'en_ruta', 'fallida', 'estado_actualizado', 'confirmada'];
        if (!in_array($normalized, $allowed, true)) {
            throw new DomainException('Invalid PackageStatus: ' . $value);
        }

        $this->value = $normalized;
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->value === 'confirmada';
    }

    /**
     * @param PackageStatus $next
     * @return bool
     */
    public function canTransitionTo(PackageStatus $next): bool
    {
        if (!$this->isCompleted()) {
            return true;
        }

        return $next->value() === 'confirmada';
    }
}
