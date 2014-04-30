<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\Options\ModuleOptions;

/**
 * Calculate hours in various periods
 *
 * Class PeriodService
 * @package JhFlexiTime\Service
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class PeriodService implements PeriodServiceInterface
{

    /**
     * Full Month - When wanting to count every day in the month
     */
    const FULL_MONTH = 'fullMonth';

    /**
     * Partial Month - When wanting to count every day up to given day
     */
    const MONTH_TO_DATE = 'monthToDate';

    /**
     * @var \JhFlexiTime\Options\ModuleOptions
     */
    protected $options;

    /**
     * @param ModuleOptions $options
     */
    public function __construct(ModuleOptions $options)
    {
        $this->options = $options;
    }

    /**
     * Create a date period, depending on the the type given
     *
     * @param \DateTime $date
     * @param string $type
     * @return \DatePeriod
     * @throws \InvalidArgumentException
     */
    public function getPeriod(\DateTime $date, $type)
    {
        switch($type) {
            case self::MONTH_TO_DATE:
                return new \DatePeriod(
                    new \DateTime(sprintf('first day of %s', $date->format('F Y'))),
                    new \DateInterval('P1D'),
                    new \DateTime(sprintf('%s 23:59:59', $date->format('d M Y')))
                );
                break;
            case self::FULL_MONTH:
                return new \DatePeriod(
                    new \DateTime(sprintf('first day of %s', $date->format('F Y'))),
                    new \DateInterval('P1D'),
                    new \DateTime(sprintf('last day of %s 23:59:59', $date->format('F Y')))
                );
                break;
        }

        throw new \InvalidArgumentException("Type is invalid");
    }

    /**
     * Get total hours in period, using config
     * to determine how many hours to count in each day
     * exclude weekends
     *
     * @param \DatePeriod $period
     * @return int
     */
    protected function getTotalHoursInPeriod(\DatePeriod $period)
    {
        $count = 0;
        foreach ($period as $day) {
            //exclude weekends
            if ($day->format('N') < 6) {
                $count++;
            }
        }

        $monthTotalHours = $count * $this->options->getHoursInDay();
        //round to 2 decimal places
        return number_format($monthTotalHours, 2, '.', '');
    }

    /**
     * @param \DateTime $month
     * @return int
     */
    public function getTotalHoursInMonth(\DateTime $month)
    {
        return $this->getTotalHoursInPeriod($this->getPeriod($month, self::FULL_MONTH));
    }

    /**
     * @param \DateTime $month
     * @return int
     */
    public function getTotalHoursToDateInMonth(\DateTime $month)
    {
        return $this->getTotalHoursInPeriod($this->getPeriod($month, self::MONTH_TO_DATE));
    }

    /**
     * Calculate the hours between to dates
     * return something like 7.5, 8.25
     *
     * Minus the lunch duration from the total,
     * so only return total working hours
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @throws \InvalidArgumentException
     * @return float hour diff
     */
    public function calculateHourDiff(\DateTime $start, \DateTime $end)
    {
        if ($end <= $start) {
            throw new \InvalidArgumentException("End time should be after start time");
        }

        $lunchDuration  = $this->options->getLunchDuration();
        $diff           = $start->diff($end);
        $hours          = $diff->format('%r%h');
        $minutes        = $diff->format('%i') / 60;
        $totalHours     = ($hours + $minutes) - $lunchDuration;
        //round to 2 decimal places
        return number_format($totalHours, 2, '.', '');
    }

    /**
     * Get the remaining hours in a month
     * using the config as a base for how many hours
     * should be worked per day
     *
     * @param \DateTime $today
     * @return int
     */
    public function getRemainingHoursInMonth(\DateTime $today)
    {
        $date       = clone $today;
        $lastDay    = clone $today;
        $date->modify("+1 day");
        $lastDay->modify('last day of this month');
        $lastDay->modify("+1 day"); //hack to include the last day in the period

        $period = new \DatePeriod($date, new \DateInterval('P1D'), $lastDay);
        return $this->getTotalHoursInPeriod($period);
    }

    /**
     * Get an array of all the dates of the week the given date
     * is in
     *
     * @param \DateTime $date
     * @return \DateTime[]
     * @throws \Exception
     */
    public function getDaysInWeek(\DateTime $date)
    {
        $weeks = $this->getWeeksInMonth($date);

        foreach($weeks as $week) {
            $firstDayOfWeek = reset($week);
            $lastDayOfWeek  = end($week);

            if($date >= $firstDayOfWeek && $date <= $lastDayOfWeek) {
                return $week;
            }
        }

        throw new \Exception("Day is not present in returned month");
    }

    /**
     * Get an array of the first and last day of
     * the week the given day is in
     *
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     */
    public function getFirstAndLastDayOfWeek(\DateTime $date)
    {
        $week = $this->getDaysInWeek($date);

        $firstDayOfWeek = reset($week);
        $lastDayOfWeek  = end($week);

        return ['firstDay' => $firstDayOfWeek, 'lastDay' => $lastDayOfWeek];
    }

    /**
     * Get the week which this date is in, and count the number
     * of non-working days
     *
     * @param \DateTime $date
     * @return int
     */
    public function getNumWorkingDaysInWeek(\DateTime $date)
    {
        $week = $this->getDaysInWeek($date);
        $week = $this->removeNonWorkingDays($week);
        return count($week);
    }

    /**
     * Remove any non-working days
     *
     * @param \DateTime[] $dates
     * @return \DateTime[]
     */
    public function removeNonWorkingDays(array $dates)
    {
        $workingDays = [];
        foreach($dates as $day) {
            if($day->format('N') < 6) {
                $workingDays[] = $day;
            }
        }

        return $workingDays;
    }

    /**
     * Get an array of weeks, with each day of the week in it
     *
     * @param \DateTime $date
     * @return array
     */
    public function getWeeksInMonth(\DateTime $date)
    {
        $period = new \DatePeriod(
            new \DateTime(sprintf('first day of %s', $date->format('F Y'))),
            new \DateInterval('P1D'),
            new \DateTime(sprintf('last day of %s 23:59:59', $date->format('F Y')))
        );

        $weeks = [];
        $weekCounter = 0;
        foreach ($period as $day) {
            $dayNum = $day->format('N');

            if(!isset($weeks[$weekCounter])) {
                $weeks[$weekCounter] = [$day];
            } else {
                $weeks[$weekCounter][] = $day;
            }

            if($dayNum == 7) {
                $weekCounter++;
            }
        }

        return $weeks;
    }

    /**
     * @param \DateTime $dateA
     * @param \DateTime $dateB
     * @return bool
     */
    public function isDateAfterDay(\DateTime $dateA, \DateTime $dateB)
    {
        $date = clone $dateB;
        $date->modify("23:59:59");
        return $dateA > $date;
    }

    /**
     * @param \DateTime $dateA
     * @param \DateTime $dateB
     * @return bool
     */
    public function isDateInPreviousMonth(\DateTime $dateA, \DateTime $dateB)
    {
        $date = clone $dateB;
        $date->modify('first day of this month 00:00:00');
        return $dateA < $date;
    }
}
