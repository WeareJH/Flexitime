<?php

namespace JhFlexiTime\Stdlib\Hydrator;

/**
 * This interface can be used with custom Types to ensure that the
 * proper type is set for the entity property value.
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @since   0.9.0
 * @author  Jeff Turcotte <jeff.turcotte@gmail.com>
 */
interface TypeConversionInterface
{
    /**
     * Convert the incoming hydration value to a value compatible with the custom type
     *
     * @param  mixed $value
     * @return mixed
     */
    public function convertToHydratorValue($value);
}
