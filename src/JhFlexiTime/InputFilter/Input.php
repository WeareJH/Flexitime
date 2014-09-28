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
        // Empty value needs further validation if continueIfEmpty is set
        // so don't inject NotEmpty validator which would always
        // mark that as false
        if (!$this->continueIfEmpty()) {
            $this->injectNotEmptyValidator();
        }
        $validator = $this->getValidatorChain();
        $value     = $this->getValue();

        //this is the add line to make the context use the filtered value
        $context[$this->getName()] = $value;
        $result    = $validator->isValid($value, $context);
        if (!$result && $this->hasFallback()) {
            $this->setValue($this->getFallbackValue());
            $result = true;
        }

        return $result;
    }
}
