<?php

namespace JhFlexiTime\Options;

use JhFlexiTime\DateTime\DateTime;
use Zend\Stdlib\AbstractOptions;

/**
 * Class NotificationOptions
 * @package JhFlexiTime\Options
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class NotificationOptions extends AbstractOptions
{

    /**
     * @var DateTime
     */
    protected $remindStart = '2 days ago';

    /**
     * @var string
     */
    protected $remindDays = '7 days';

    /**
     * @return DateTime
     */
    public function getRemindStart()
    {
        $date = new DateTime($this->remindStart);
        $date->setTime(0, 0, 0);
        return $date;
    }

    /**
     * @param string $remindStart
     * @throws \Exception
     */
    public function setRemindStart($remindStart)
    {
        try {
            new DateTime($remindStart);
        } catch (\Exception $e) {
            throw $e;
        }
        $this->remindStart = $remindStart;
    }

    /**
     * @return DateTime
     */
    public function getRemindDays()
    {
        return $this->remindDays;
    }

    /**
     * @param string $remindDays
     */
    public function setRemindDays($remindDays)
    {
        if (!preg_match('/^\s*\d+\s+days?\s*$/', $remindDays)) {
            throw new \InvalidArgumentException('remind_days should be like: "7 days"');
        }

        $this->remindDays = $remindDays;
    }

    /**
     * @return \JhFlexiTime\DateTime\DateTime[]
     */
    public function getRemindPeriod()
    {
        $interval = \DateInterval::createFromDateString($this->remindDays);
        return $this->getXWorkingDaysToDate($this->getRemindStart(), (int) $interval->format('%d'));
    }

    /**
     * Get an array of dates, counting backwards using
     * passed in total. Any dates which are weekends are excluded.
     * You will always get an array of dates with a count equal to that of
     * $totalDaysToCountBack
     *
     *
     * @param DateTime $toDate
     * @param int      $totalDaysToCountBack
     *
     * @return DateTime[]
     */
    private function getXWorkingDaysToDate(DateTime $toDate, $totalDaysToCountBack)
    {
        $dates      = [];
        $date       = clone $toDate;
        $date->add(new \DateInterval('P1D'));
        while ($totalDaysToCountBack > 0) {
            $date = clone $date;
            $date->sub(new \DateInterval('P1D'));

            if ($date->format('N') < 6) {
                array_unshift($dates, $date);
                $totalDaysToCountBack--;
            }
        }

        return $dates;
    }
}
