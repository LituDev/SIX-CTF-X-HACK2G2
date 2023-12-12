<?php

namespace App\Entity\Type;

use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ODM\MongoDB\Types\Type;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidType extends Type
{

    public const NAME = 'uuid';

    public function convertToPHPValue($value): ?UuidInterface
    {
        if ($value instanceof UuidInterface) {
            return $value;
        }

        if (!is_string($value) || $value === '') {
            return null;
        }

        try {
            $uuid = Uuid::fromString($value);
        } catch (\Throwable $e) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }

        return $uuid;
    }

    public function convertToDatabaseValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if(is_string($value) && !str_contains($value, '-')) {
            $value = Uuid::fromHexadecimal(new Hexadecimal($value));
        }

        if (
            $value instanceof UuidInterface
            || (
                (is_string($value)
                    || (is_object($value) && method_exists($value, '__toString')))
                && Uuid::isValid((string) $value)
            )
        ) {
            return (string) $value;
        }

        throw ConversionException::conversionFailed($value, self::NAME);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
