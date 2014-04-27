<?php

namespace JhFlexiTimeTest\Service;

use JhFlexiTime\Service\PeriodService;
use JhFlexiTime\Options\ModuleOptions;

/**
 * Class PeriodServiceTest
 * @package JhFlexiTimeTest\Service
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class PeriodServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \JhFlexiTime\Service\PeriodService
     */
    protected $periodService;

    /**
     * @var float
     */
    protected $timeInDay = 7.5;

    /**
     * @var int
     */
    protected $lunchDuration = 1;

    /**
     * @var array
     */
    protected $config = array();

    /**
     * Create Service
     */
    public function setUp()
    {
        $this->periodService = new PeriodService($this->getOptions());
    }

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        $options = new ModuleOptions();
        $options->setHoursInDay($this->timeInDay)
            ->setLunchDuration($this->lunchDuration);

        return $options;
    }

    /**
     * Function should throw exception when end date is less than start date
     */
    public function testHourDiffCalculatorThrowsExceptionWhenEndBeforeStart()
    {
        $start  = new \DateTime("10:00");
        $end    = new \DateTime("09:00");

        $this->setExpectedException('InvalidArgumentException', 'End time should be after start time');
        $this->periodService->calculateHourDiff($start, $end);
    }

    /**
     * Function should throw exception when end date is same as start date
     */
    public function testHourDiffCalculatorThrowsExceptionWhenEndSameAsStart()
    {
        $start  = new \DateTime("10:00");
        $end    = new \DateTime("10:00");

        $this->setExpectedException('InvalidArgumentException', 'End time should be after start time');
        $this->periodService->calculateHourDiff($start, $end);
    }

    /**
     * Test that when a new total is set, the getRunningTotal function
     * returns the difference between the new balance and the old balance,
     * minus the lunch duration
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @param int $expected
     *
     * @dataProvider hourDiffProvider
     */
    public function testHourDiffCalculator($start, $end, $expected)
    {
        $totalHours = $this->periodService->calculateHourDiff($start, $end);
        $this->assertEquals($expected, $totalHours);
    }

    /**
     * Provider for Start & End Times - incl expected hour diff
     *
     * TODO: test with different lunch durations
     * @return array
     */
    public function hourDiffProvider()
    {
        /**
         *  Start Time | End Time | Expected Hour Diff
         */
        return array(
            array(new \DateTime("09:00"),   new \DateTime("10:00"), 0),
            array(new \DateTime("09:00"),   new \DateTime("9:30"), -0.5),
            array(new \DateTime("09:00"),   new \DateTime("17:30"), 7.5),
            array(new \DateTime("07:00"),   new \DateTime("17:30"), 9.5),
            //only calculates hours not days
            array(new \DateTime("yesterday 09:00"),   new \DateTime("17:30"), 7.5),
            array(new \DateTime("yesterday 09:00"),   new \DateTime("tomorrow 17:30"), 7.5),

            array(new \DateTime("09:15"),   new \DateTime("17:30"), 7.25),
            array(new \DateTime("09:00"),   new \DateTime("17:45"), 7.75),
            array(new \DateTime("09:20"),   new \DateTime("17:00"), 6.67),
            array(new \DateTime("09:25"),   new \DateTime("17:00"), 6.58),
        );
    }

    /**
     * @param \DateTime $date
     * @param int $expectedTotal
     *
     * @dataProvider remainingHoursProvider
     */
    public function testRemainingHoursInMonth(\DateTime $date, $expectedTotal)
    {
        $remainingHours = $this->periodService->getRemainingHoursInMonth($date);
        $this->assertEquals($expectedTotal, $remainingHours);
    }

    /**
     * @return array
     */
    public function remainingHoursProvider()
    {
        /**
         *  Date | Expected Remaining Hours
         */
        return array(
            array(new \DateTime("10 March 2014"),   112.5),
            array(new \DateTime("01 January 2014"), 165.00),
            array(new \DateTime("27 April 2014"),   22.5),
        );
    }

    /**
     * @param \DateTime $month
     * @param $expectedTotal
     *
     * @dataProvider monthProvider
     */
    public function testGetTotalHoursInMonth(\DateTime $month, $expectedTotal)
    {
        $hours = $this->periodService->getTotalHoursInMonth($month);
        $this->assertEquals($expectedTotal, $hours);
    }

    /**
     * @return array
     */
    public function monthProvider()
    {
        /**
         *  Date | Expected Total Month Hours
         */
        return array(
            array(new \DateTime("10 March 2014"), 157.5),
            array(new \DateTime("01 March 2014 00:00"), 157.5),
            array(new \DateTime("31 March 2014 23:59:59"), 157.5),
            array(new \DateTime("01 April 1988"), 157.5),
            array(new \DateTime("08 February 2011"), 150),
        );
    }

    /**
     * @param \DateTime $month
     * @param $expectedTotal
     *
     * @dataProvider monthToDateProvider
     */
    public function testGetTotalHoursToDateInMonth(\DateTime $month, $expectedTotal)
    {
        $hours = $this->periodService->getTotalHoursToDateInMonth($month);
        $this->assertEquals($expectedTotal, $hours);
    }

    /**
     * @return array
     */
    public function monthToDateProvider()
    {
        /**
         *  Date | Expected Total Month Hours
         */
        return array(
            array(new \DateTime("10 March 2014"), 45),
            array(new \DateTime("01 March 2014 00:00"), 0),
            array(new \DateTime("31 March 2014 23:59:59"), 157.5),
            array(new \DateTime("01 April 1988"), 7.5),
            array(new \DateTime("08 February 2011"), 45),
        );
    }
}
