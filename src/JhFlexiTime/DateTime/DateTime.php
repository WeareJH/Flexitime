<?php

namespace JhFlexiTime\DateTime;

/**
 * Class DateTime
 * @package JhFlexiTime\DateTime
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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

    /**
     * Check if given date is in same month and year
     *
     * @param DateTime $date
     * @return bool
     */
    public function isSameMonthAndYear(DateTime $date)
    {
        return $this->format('m-Y') === $date->format('m-Y');
    }
}
