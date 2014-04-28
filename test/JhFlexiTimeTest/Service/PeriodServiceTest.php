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

    /**
     * @param \DateTime $date
     * @param array $expected
     * @dataProvider firstAndLastDayOfWeekProvider
     */
    public function testGetFirstAndLastDayOfWeek(\DateTime $date, array $expected)
    {
        $result = $this->periodService->getFirstAndLastDayOfWeek($date);
        $this->assertEquals($expected, $result);
    }

    public function firstAndLastDayOfWeekProvider()
    {
        return [
            [new \DateTime("28 April 2014"),    ['firstDay' => new \DateTime("28th April 2014"),    'lastDay' => new \DateTime("30th April 2014")]],
            [new \DateTime("1 May 2014"),       ['firstDay' => new \DateTime("1 May 2014"),         'lastDay' => new \DateTime("4 May 2014")]],
            [new \DateTime("29 February 2012"), ['firstDay' => new \DateTime("27 February 2012"),   'lastDay' => new \DateTime("29 February 2012")]],
            [new \DateTime("12 November 2014"), ['firstDay' => new \DateTime("10 November 2014"),   'lastDay' => new \DateTime("16 November 2014")]],
            [new \DateTime("10 December 2014"), ['firstDay' => new \DateTime("8 December 2014"),    'lastDay' => new \DateTime("14 December 2014")]],
        ];

    }

    /**
     * @param \DateTime $date
     * @param $expected
     * @dataProvider getWeeksInMonthProvider
     */
    public function testGetWeeksInMonth(\DateTime $date, $expected)
    {
        $weeks = $this->periodService->getWeeksInMonth($date);
        $this->assertEquals($expected, $weeks);
    }

    public function getWeeksInMonthProvider()
    {

        $April2014periods = [
            $this->createPeriod("1 April 2014", "6 April 2014"),
            $this->createPeriod("7 April 2014", "13 April 2014"),
            $this->createPeriod("14 April 2014", "20 April 2014"),
            $this->createPeriod("21 April 2014", "27 April 2014"),
            $this->createPeriod("28 April 2014", "30 April 2014"),
        ];

        $march2011Periods = [
            $this->createPeriod("1 March 2011", "6 March 2011"),
            $this->createPeriod("7 March 2011", "13 March 2011"),
            $this->createPeriod("14 March 2011", "20 March 2011"),
            $this->createPeriod("21 March 2011", "27 March 2011"),
            $this->createPeriod("28 March 2011", "31 March 2011"),
        ];

        $february2011Periods = [
            $this->createPeriod("1 February 2011", "6 February 2011"),
            $this->createPeriod("7 February 2011", "13 February 2011"),
            $this->createPeriod("14 February 2011", "20 February 2011"),
            $this->createPeriod("21 February 2011", "27 February 2011"),
            $this->createPeriod("28 February 2011", "28 February 2011"),
        ];

        return [
            [new \DateTime("April 28 2014"), $April2014periods],
            [new \DateTime("March 28 2011"), $march2011Periods],
            [new \DateTime("February 1 2011"), $february2011Periods],
        ];
    }

    /**
     * Helper function to generate a \DatePeriod object
     *
     * @param string $start
     * @param string $end
     * @return array
     */
    public function createPeriod($start, $end)
    {
        //hack to include last day in DatePeriod
        $end = new \DateTime($end);
        $end->modify( '+1 day' );
        $period = new \DatePeriod(new \DateTime($start), new \DateInterval('P1D'), $end);

        return iterator_to_array($period);
    }

    public function testRemoveNonWorkingDays()
    {
        $period = $this->createPeriod("1 April 2014", "30 April 2014");
        $dates = $this->periodService->removeNonWorkingDays($period);

        foreach($dates as $day) {
            $this->assertLessThan(6, $day->format('N'));
        }
    }

    /**
     * @param \DateTime $date
     * @param $expected
     * @dataProvider daysInWeekProvider
     */
    public function testGetDaysInWeek($date, $expected)
    {
        $result = $this->periodService->getDaysInWeek($date);
        $this->assertEquals($expected, $result);
    }

    public function daysInWeekProvider()
    {
        return [
            [new \DateTime("6 April 2014"), $this->createPeriod("1 April 2014", "6 April 2014")],
            [new \DateTime("12 June 2014"), $this->createPeriod("9 June 2014", "15 June 2014")],
        ];
    }

    public function testGetNumWorkingDaysInWeek()
    {
        $date = new \DateTime("6 April 2014");
        $this->assertSame(4, $this->periodService->getNumWorkingDaysInWeek($date));
    }

    public function testGetPeriodThrowsExceptionIfInvalidTypePassedIn()
    {
        $this->setExpectedException('InvalidArgumentException', 'Type is invalid');
        $this->periodService->getPeriod(new \DateTime, 'NOTAVALIDTYPE');
    }

    public function testGetDaysInWeekThrowsExceptionIfDateNotInAnyWeek()
    {
        $date = new \DateTime("6 April 2014");

        $this->periodService = $this->getMock('JhFlexiTime\Service\PeriodService', ['getWeeksInMonth'], [$this->getOptions()]);
        $this->periodService
            ->expects($this->once())
            ->method('getWeeksInMonth')
            ->with($date)
            ->will($this->returnValue([[new \DateTime("1 January 2014")]]));

        $this->setExpectedException("Exception", "Day is not present in returned month");
        $this->periodService->getDaysInWeek($date);
    }

}
