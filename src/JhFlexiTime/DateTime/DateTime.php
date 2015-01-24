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

    /**
     * Immutable function to get the start of this month
     *
     * @return DateTime
     */
    public function startOfMonth()
    {
        $date = clone $this;
        $date->modify('first day of this month 00:00:00');
        return $date;
    }

    /**
     * Immutable function to get the end of this month
     *
     * @return DateTime
     */
    public function endOfMonth()
    {
        $date = clone $this;
        $date->modify('last day of this month 23:59:59');
        return $date;
    }
}
