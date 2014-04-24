<?php

namespace JhFlexiTime\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * Class BookingOptions
 * @package JhFlexiTime\Options
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingOptions extends AbstractOptions implements BookingOptionsInterface
{

    protected $__strictMode__ = false;

    /**
     * @var false|string
     */
    protected $minStartTime = false;

    /**
     * @var false|string
     */
    protected $maxStartTime = false;

    /**
     * @var false|string
     */
    protected $minEndTime = false;

    /**
     * @var false|string
     */
    protected $maxEndTime = false;

    /**
     * @return bool|string
     */
    public function getMinStartTime()
    {
        return $this->minStartTime;
    }

        /**
     * @return bool|string
     */
    public function getMaxStartTime()
    {
        return $this->maxStartTime;
    }

    /**
     * @return bool|string
     */
    public function getMinEndTime()
    {
        return $this->minEndTime;
    }

    /**
     * @return bool|string
     */
    public function getMaxEndTime()
    {
        return $this->maxEndTime;
    }

    /**
     * @param string $key
     * @param string|false $value
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function __set($key, $value)
    {
        $property = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
        if(property_exists($this, $property)) {

            if($this->validateTime($value)) {
                $this->$property = $value;
            } else {
                throw new \InvalidArgumentException(sprintf("%s should be a 24 hour time in the format HH:MM", $key));
            }
        } else {
            throw new \BadMethodCallException(
                sprintf('The option "%s" is not a valid property', $key)
            );
        }
    }

    /**
     * @param string $time
     * @return bool
     */
    public function validateTime($time)
    {
        if($time === false) {
            return true;
        }

        if(preg_match("/(2[0-3]|[01][0-9]):[0-5][0-9]/", $time)) {
            return true;
        }

        return false;
    }
} 