<?php

namespace JhFlexiTime\InputFilter;

use Zend\InputFilter\InputFilter as ZfBaseInputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputInterface;
use Zend\InputFilter\EmptyContextInterface;

/**
 * Class BaseInputFilter
 * @package JhFlexiTime\InputFilter
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BaseInputFilter extends ZfBaseInputFilter
{
    /**
     * Extension so we get the filtered values not the raw values
     *
     * @param array $inputs
     * @param array $data
     * @return bool
     */
    protected function validateInputs(array $inputs, array $data = array())
    {
        // backwards compatibility
        if (empty($data)) {
            //this is the line changed from base class
            $data = $this->getValues();
        }
        $this->validInputs = array();
        $this->invalidInputs = array();
        $valid = true;
        foreach ($inputs as $name) {
            $input = $this->inputs[$name];
            $dataExists = array_key_exists($name, $data);

            // key doesn't exist, but input is not required; valid
            if (!$dataExists
                && $input instanceof InputInterface
                && !$input->isRequired()
            ) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // key doesn't exist, input is required, allows empty; valid if
            // continueIfEmpty is false or input doesn't implement
            // that interface; otherwise validation chain continues
            if (!$dataExists
                && $input instanceof InputInterface
                && $input->isRequired()
                && $input->allowEmpty()
            ) {
                if (!($input instanceof EmptyContextInterface && $input->continueIfEmpty())) {
                    $this->validInputs[$name] = $input;
                    continue;
                }
            }

            // key exists, is null, input is not required; valid
            if ($dataExists
                && null === $data[$name]
                && $input instanceof InputInterface
                && !$input->isRequired()
            ) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // key exists, is null, input is required, allows empty; valid if
            // continueIfEmpty is false or input doesn't implement
            // that interface; otherwise validation chain continues
            if ($dataExists
                && null === $data[$name]
                && $input instanceof InputInterface
                && $input->isRequired()
                && $input->allowEmpty()
            ) {
                if (!($input instanceof EmptyContextInterface && $input->continueIfEmpty())) {
                    $this->validInputs[$name] = $input;
                    continue;
                }
            }

            // key exists, empty string, input is not required, allows empty; valid
            if ($dataExists
                && '' === $data[$name]
                && $input instanceof InputInterface
                && !$input->isRequired()
                && $input->allowEmpty()
            ) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // key exists, empty string, input is required, allows empty; valid
            // if continueIfEmpty is false, otherwise validation continues
            if ($dataExists
                && '' === $data[$name]
                && $input instanceof InputInterface
                && $input->isRequired()
                && $input->allowEmpty()
            ) {
                if (!($input instanceof EmptyContextInterface && $input->continueIfEmpty())) {
                    $this->validInputs[$name] = $input;
                    continue;
                }
            }

            // key exists, is array representing file, no file present, input not
            // required or allows empty; valid
            if ($dataExists
                && is_array($data[$name])
                && (
                    (isset($data[$name]['error'])
                        && $data[$name]['error'] === UPLOAD_ERR_NO_FILE)
                    || (count($data[$name]) === 1
                        && isset($data[$name][0])
                        && is_array($data[$name][0])
                        && isset($data[$name][0]['error'])
                        && $data[$name][0]['error'] === UPLOAD_ERR_NO_FILE)
                )
                && $input instanceof InputInterface
                && (!$input->isRequired() || $input->allowEmpty())
            ) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // make sure we have a value (empty) for validation
            if (!$dataExists) {
                $data[$name] = null;
            }

            // Validate an input filter
            if ($input instanceof InputFilterInterface) {
                if (!$input->isValid()) {
                    $this->invalidInputs[$name] = $input;
                    $valid = false;
                    continue;
                }
                $this->validInputs[$name] = $input;
                continue;
            }

            // Validate an input
            if ($input instanceof InputInterface) {
                if (!$input->isValid($data)) {
                    // Validation failure
                    $this->invalidInputs[$name] = $input;
                    $valid = false;
                    if ($input->breakOnFailure()) {
                        return false;
                    }
                    continue;
                }
                $this->validInputs[$name] = $input;
                continue;
            }
        }
        return $valid;
    }
}
