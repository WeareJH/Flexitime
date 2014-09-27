<?php

namespace JhFlexiTime\DateTime;

/**
 * Class DateTime
 * @package JhFlexiTime\DateTime
 */
class DateTime extends \DateTime
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->format('U');
    }
}
