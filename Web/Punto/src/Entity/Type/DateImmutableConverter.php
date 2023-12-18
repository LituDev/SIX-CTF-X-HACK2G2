<?php

namespace App\Entity\Type;

use GraphAware\Neo4j\OGM\Converters\Converter;
use GraphAware\Neo4j\OGM\Exception\ConverterException;

class DateImmutableConverter extends Converter
{
    private const DEFAULT_FORMAT = 'timestamp';

    private const LONG_TIMESTAMP_FORMAT = 'long_timestamp';

    public function getName(): string
    {
        return 'datetime_immutable';
    }

    public static function register() : void {
        self::addConverter('datetime_immutable', self::class);
    }

    public function toDatabaseValue($value, array $options): float|int|string|null
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof \DateTimeImmutable) {
            $format = $options['format'] ?? self::DEFAULT_FORMAT;

            if (self::DEFAULT_FORMAT === $format) {
                return $value->getTimestamp();
            }

            if (self::LONG_TIMESTAMP_FORMAT === $format) {
                return $value->getTimestamp() * 1000;
            }

            try {
                return $value->format($format);
            } catch (\Exception $e) {
                throw new ConverterException(sprintf('Error while converting timestamp: %s', $e->getMessage()));
            }
        }

        throw new ConverterException(sprintf('Unable to convert value in converter "%s"', $this->getName()));
    }

    public function toPHPValue(array $values, array $options): \DateTimeImmutable|false|null
    {
        if (!isset($values[$this->propertyName])) {
            return null;
        }

        $tz = isset($options['timezone'])
            ? new \DateTimeZone($options['timezone'])
            : new \DateTimeZone(date_default_timezone_get());

        $format = $options['format'] ?? self::DEFAULT_FORMAT;
        $v = $values[$this->propertyName];

        if (self::DEFAULT_FORMAT === $format) {
            return \DateTimeImmutable::createFromFormat('U', $v, $tz);
        }

        if (self::LONG_TIMESTAMP_FORMAT === $format) {
            return \DateTimeImmutable::createFromFormat('U', (string)round($v / 1000), $tz);
        }

        return \DateTimeImmutable::createFromFormat($format, $v);
    }
}
