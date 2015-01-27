<?php

namespace JhFlexiTime\Options;

use JhFlexiTime\DateTime\DateTime;
use Zend\Stdlib\AbstractOptions;

/**
 * Class ModuleOptions
 * @package JhFlexiTime\Options
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * @var float
     */
    protected $hoursInDay = 7.5;

    /**
     * @var bool
     */
    protected $skipWeekends = true;

    /**
     * @var float
     */
    protected $lunchDuration = 1;

    /**
     * @var bool
     */
    protected $cappedCreditEnabled = false;

    /**
     * @var array
     */
    protected $creditCaps = [];

    /**
     * @param float $hoursInDay
     * @return \JhFlexiTime\Options\ModuleOptions
     */
    public function setHoursInDay($hoursInDay)
    {
        $this->hoursInDay = $hoursInDay;
        return $this;
    }

    /**
     * @return float
     */
    public function getHoursInDay()
    {
        return $this->hoursInDay;
    }

    /**
     * @param float $lunchDuration
     * @return \JhFlexiTime\Options\ModuleOptions
     */
    public function setLunchDuration($lunchDuration)
    {
        $this->lunchDuration = $lunchDuration;
        return $this;
    }

    /**
     * @return float
     */
    public function getLunchDuration()
    {
        return $this->lunchDuration;
    }

    /**
     * @param boolean $skipWeekends
     * @return \JhFlexiTime\Options\ModuleOptions
     */
    public function setSkipWeekends($skipWeekends)
    {
        $this->skipWeekends = $skipWeekends;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSkipWeekends()
    {
        return $this->skipWeekends;
    }

    /**
     * @return boolean
     */
    public function skipWeekends()
    {
        return $this->skipWeekends;
    }

    /**
     * @param bool $enabled
     */
    public function setCreditCapEnabled($enabled)
    {
        $this->cappedCreditEnabled = $enabled;
    }

    /**
     * @return bool
     */
    public function getCreditCapEnabled()
    {
        return $this->cappedCreditEnabled;
    }

    /**
     * @return bool
     */
    public function creditCapEnabled()
    {
        return $this->cappedCreditEnabled;
    }

    /**
     * @param array $caps
     */
    public function setCreditCaps(array $caps)
    {
        $months = [];
        foreach ($caps as $month => $capLimit) {
            if (!is_string($month) || !preg_match('/^(0[1-9])|^(1[0-2])-\d{4}$/', $month)) {
                throw new \InvalidArgumentException(sprintf('Date should be in the format m-Y. Given: %s', $month));
            }

            $months[] = array(
                'month' => new DateTime(sprintf('01-%s 00:00:00', $month)),
                'limit' => $capLimit
            );

            usort($months, function($a, $b) {
                $dateA = $a['month'];
                $dateB = $b['month'];

                if ($dateA == $dateB) {
                    return 0;
                }
                return $dateA > $dateB ? 1 : -1;
            });
        }

        $this->creditCaps = $months;
    }

    /**
     * @return array
     */
    public function getCreditCaps()
    {
        return $this->creditCaps;
    }

    /**
     * @param DateTime $date
     *
     * @return float|null
     */
    public function getCreditCapForDate(DateTime $date)
    {
        foreach (array_reverse($this->creditCaps) as $index => $creditCap) {
            if ($date >= $creditCap['month']) {
                return $creditCap['limit'];
            }
        }
        return null;
    }
}
