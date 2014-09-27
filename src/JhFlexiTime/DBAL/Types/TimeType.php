<?php

namespace JhFlexiTime\DBAL\Types;

use Doctrine\DBAL\Types\TimeType as DoctrineTimeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Stdlib\Hydrator\TypeConversionInterface;

/**
 * Class TimeType
 * @package JhFlexiTime\DBAL\Types
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class TimeType extends DoctrineTimeType implements TypeConversionInterface
{
    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return DateTime|mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $dateTime = parent::convertToPHPValue($value, $platform);

        if (!$dateTime) {
            return $dateTime;
        }

        $return = new DateTime();
        $return->setTimestamp($dateTime->getTimestamp());
        return $return;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'time';
    }

    /**
     * @param string $value
     * @return DateTime
     */
    public function convertToHydratorValue($value)
    {
        if ('' === $value) {
            return null;
        }

        if (is_int($value)) {
            $dateTime = new DateTime();
            $dateTime->setTimestamp($value);
            $value = $dateTime;
        } elseif (is_string($value)) {
            $value = new DateTime($value);
        }

        return $value;
    }
}
