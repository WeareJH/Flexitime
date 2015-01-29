<?php

namespace JhFlexiTime\InputFilter;

use Zend\InputFilter\Input as ZfBaseInput;

/**
 * Class Input
 * @package JhFlexiTime\InputFilter
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Input extends ZfBaseInput
{
    /**
     * Overridden to set the context to use the filtered value
     * Eg - Dates will actually be DateTime instances
     *
     *
     * @param  mixed $context Extra "context" to provide the validator
     * @return bool
     */
    public function isValid($context = null)
    {
        //make the context use the filtered value
        $context[$this->getName()] =  $this->getValue();
        return parent::isValid($context);
    }
}
