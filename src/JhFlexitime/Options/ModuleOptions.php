<?php

namespace JhFlexiTime\Options;

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
}
