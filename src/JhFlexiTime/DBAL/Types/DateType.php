<?php

namespace JhFlexiTime\DBAL\Types;

use Doctrine\DBAL\Types\DateType as DoctrineDateType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class DateType
 * @package JhFlexiTime\DBAL\Types
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DateType extends DoctrineDateType
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
        return 'date';
    }
}
