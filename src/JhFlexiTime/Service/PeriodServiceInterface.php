<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\DateTime\DateTime;

/**
 * Interface PeriodServiceInterface
 * @package JhFlexiTime\Service
 * @author Aydin Hassan <aydin@wearejh.com>
 */
interface PeriodServiceInterface
{
    /**
     * Get total hours in a given month
     *
     * @param DateTime $month
     * @return float
     */
    public function getTotalHoursInMonth(DateTime $month);

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @return float
     */
    public function getTotalHoursBetweenDates(DateTime $start, DateTime $end);

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @return float
     */
    public function calculateHourDiff(DateTime $start, DateTime $end);

    /**
     * @param DateTime $today
     * @return float
     */
    public function getRemainingHoursInMonth(DateTime $today);

    /**
     * @param DateTime $date
     * @return array
     */
    public function getWeeksInMonth(DateTime $date);

    /**
     * @param DateTime $dateA
     * @param DateTime $dateB
     * @return bool
     */
    public function isDateAfterDay(DateTime $dateA, DateTime $dateB);
}
