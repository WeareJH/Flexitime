<?php

namespace JhFlexiTime\Stdlib\Hydrator;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as BaseDoctrineObject;
use Doctrine\DBAL\Types\Type;

/**
 * Class DoctrineObject
 * @package JhFlexiTime\Stdlib\Hydrator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DoctrineObject extends BaseDoctrineObject
{
    /**
     * @param mixed $value
     * @param string $typeOfField
     * @return mixed
     */
    protected function handleTypeConversions($value, $typeOfField)
    {
        if (Type::hasType($typeOfField)) {
            $type = Type::getType($typeOfField);
            if ($type instanceof TypeConversionInterface) {
                return $type->convertToHydratorValue($value);
            }
        }

        return parent::handleTypeConversions($value, $typeOfField);
    }
}
