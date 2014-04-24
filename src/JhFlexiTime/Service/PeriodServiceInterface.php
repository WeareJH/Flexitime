<?php

namespace JhFlexiTime\Service;

/**
 * Interface PeriodServiceInterface
 * @package JhFlexiTime\Service
 * @author Aydin Hassan <aydin@wearejh.com>
 */
interface PeriodServiceInterface
{
    /**
     * @param \DateTime $month
     * @return float
     */
    public function getTotalHoursInMonth(\DateTime $month);

    /**
     * @param \DateTime $month
     * @return float
     */
    public function getTotalHoursToDateInMonth(\DateTime $month);

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return float
     */
    public function calculateHourDiff(\DateTime $start, \DateTime $end);

    /**
     * @param \DateTime $today
     * @return float
     */
    public function getRemainingHoursInMonth(\DateTime $today);
}
