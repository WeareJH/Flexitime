<?php

namespace JhFlexiTime\Validator;

use Doctrine\Common\Persistence\Mapping\MappingException;
use DoctrineModule\Validator\UniqueObject as DoctrineUniqueObject;
use ZfcUser\Entity\UserInterface;

/**
 * Class UniqueObject
 * @package JhFlexiTime\Validator
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class UniqueObject extends DoctrineUniqueObject
{

    /**
     * Returns false if there is another object with the same field values but other identifiers.
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        if (!$this->useContext) {
            $context    = (array) $value;
        } else {
            $value      = (array) $context;
        }

        $value = $this->cleanSearchValue($value);
        $match = $this->objectRepository->findOneBy($value);

        if (!is_object($match)) {
            return true;
        }

        $expectedIdentifiers = $this->getExpectedIdentifiers($context);
        $foundIdentifiers    = $this->getFoundIdentifiers($match);

        foreach ($foundIdentifiers as $key => $idField) {
            if (is_object($idField)) {
                try {
                    $classMeta  = $this->objectManager->getClassMetadata(get_class($idField));
                    $idValue    = $classMeta->getIdentifierValues($idField);
                    $idValue    = array_pop($idValue);
                    $foundIdentifiers[$key] = $idValue;
                } catch (MappingException $e) {
                    //not an object managed by doctrine
                    continue;
                }
            }
        }

        if (count(array_diff_assoc($expectedIdentifiers, $foundIdentifiers)) == 0) {
            return true;
        }

        $this->error(self::ERROR_OBJECT_NOT_UNIQUE, $value);
        return false;
    }
}
