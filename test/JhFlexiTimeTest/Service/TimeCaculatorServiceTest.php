<?php

namespace JhFlexiTimeTest\Service;

use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Service\TimeCalculatorService;
use ZfcUser\Entity\User;

/**
 * Class TimeCalculatorServiceTest
 * @package JhFlexiTimeTest\Service
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class TimeCalculatorServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \JhFlexiTime\Service\TimeCalculatorService
     */
    protected $timeCalculatorService;

    /**
     * @var float
     */
    protected $timeInDay = 7.5;

    /**
     * @var int
     */
    protected $lunchDuration = 1;

    /**
     * @var \JhFlexiTime\Options\ModuleOptions
     */
    protected $options;

    /**
     * @var \DateTime $date
     */
    protected $date ;

    /**
     * @var \JhFlexiTime\Repository\BookingRepositoryInterface
     */
    protected $bookingRepository;

    /**
     * @var \JhFlexiTime\Service\BalanceServiceInterface
     */
    protected $balanceService;

    protected $balanceRepository;

    /**
     * @var \JhFlexiTime\Service\PeriodService
     */
    protected $periodService;

    /**
     * Create Service
     */
    public function setUp()
    {
        $this->date                 = new \DateTime;
        $this->bookingRepository    = $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface');
        $this->balanceRepository    = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');
        $this->periodService        = $this->getMockBuilder('JhFlexiTime\Service\PeriodService')
                                         ->disableOriginalConstructor()
                                         ->getMock();

        $this->options = new ModuleOptions();
        $this->options->setHoursInDay($this->timeInDay)
                      ->setLunchDuration($this->lunchDuration);
    }

    /**
     * @return TimeCalculatorService
     */
    public function getService()
    {
        $timeCalculatorService = new TimeCalculatorService(
            $this->options,
            $this->bookingRepository,
            $this->balanceRepository,
            $this->periodService,
            $this->date
        );

        return $timeCalculatorService;
    }

    /**
     * @param float $initialBalance
     * @param float $expectedBalance
     * @param float $monthToDateTotalHours
     * @param float $bookedToDate
     *
     * @dataProvider runningBalanceProvider
     */
    public function testGetRunningBalance($initialBalance, $expectedBalance, $monthToDateTotalHours, $bookedToDate)
    {

        $mockUser       = $this->getMock('ZfcUser\Entity\UserInterface');
        $runningBalance = new RunningBalance();
        $runningBalance->setBalance($initialBalance);

        $this->balanceRepository->expects($this->once())
            ->method('findOneByUser')
            ->with($mockUser)
            ->will($this->returnValue($runningBalance));

        $this->periodService->expects($this->once())
            ->method('getTotalHoursToDateInMonth')
            ->with($this->date)
            ->will($this->returnValue($monthToDateTotalHours));

        $this->bookingRepository->expects($this->once())
             ->method('getMonthBookedToDateTotalByUser')
             ->with($mockUser, $this->date)
             ->will($this->returnValue($bookedToDate));

        $balance = $this->getService()->getRunningBalance($mockUser);
        $this->assertEquals($expectedBalance, $balance);
    }

    /**
     * @return array
     */
    public function runningBalanceProvider()
    {
        /**
         * Initial Balance | Expected Balance | Month Hours So Far | Month Booked
         */
        return [
            [0,     -5,     15,     10],
            [0,     0,      15,     15],
            [5,     0,      15,     10],
            [5,     5,      15,     15],
            [-5,    0,      10,     15],
            [-5,    -5,     15,     15],
            [-5,    -10,    15,     10],
            [-50,   -50,    150,    150],
            [-50,   -100,   150,    100],
            [50,    100,    150,    200],
            [0,     -1.75,  20,     18.25],
            [-10.5, 0,      20,     30.5],

        ];
    }

    public function testGetBalanceForwardReturnsZeroIfNoRowPresent()
    {
        $user = new User;

        $this->balanceRepository
             ->expects($this->once())
             ->method('findOneByUser')
             ->with($user)
             ->will($this->returnValue(null));

        $this->assertEquals(0, $this->getService()->getBalanceForward($user));
    }

    public function testGetBalanceForwardReturnsRunningBalance()
    {
        $balance    = 25;
        $user       = new User;
        $runningBalance = new RunningBalance();
        $runningBalance->setBalance($balance);

        $this->balanceRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->returnValue($runningBalance));

        $this->assertEquals($balance, $this->getService()->getBalanceForward($user));
    }

    public function testGetMonthBalanceWithPreviousMonthReturnsFullMonthBalance()
    {
        $user = new User;
        $this->date = new \DateTime("15 May 2014");
        $date = new \DateTime("4 April 2014");

        $this->bookingRepository
             ->expects($this->once())
             ->method('getMonthBookedTotalByUser')
             ->with($user, $date)
             ->will($this->returnValue(40));

        $this->periodService
             ->expects($this->once())
             ->method('getTotalHoursInMonth')
             ->with($date)
             ->will($this->returnValue(50));

        $this->assertEquals(-10, $this->getService()->getMonthBalance($user, $date));
    }

    public function testGetMonthBalanceWithSameMonthReturnsToDateBalance()
    {
        $user = new User;
        $this->date = new \DateTime("15 May 2014");
        $date = new \DateTime("4 May 2014");

        $this->bookingRepository
            ->expects($this->once())
            ->method('getMonthBookedToDateTotalByUser')
            ->with($user, $this->date)
            ->will($this->returnValue(40));

        $this->periodService
            ->expects($this->once())
            ->method('getTotalHoursToDateInMonth')
            ->with($this->date)
            ->will($this->returnValue(50));

        $this->assertEquals(-10, $this->getService()->getMonthBalance($user, $date));
    }

    public function testGetMonthBalanceWithFutureMonthDateReturnsZero()
    {
        $user = new User;
        $this->date = new \DateTime("15 May 2014");
        $date = new \DateTime("16 June 2014");
        $this->assertEquals(0, $this->getService()->getMonthBalance($user, $date));
    }

    public function testGetMonthTotalWorkedHoursForPreviousMonth()
    {
        $user = new User;
        $this->date = new \DateTime("15 May 2014");
        $date = new \DateTime("4 April 2014");

        $this->bookingRepository
            ->expects($this->once())
            ->method('getMonthBookedTotalByUser')
            ->with($user, $date)
            ->will($this->returnValue(40));

        $this->assertEquals(40, $this->getService()->getMonthTotalWorked($user, $date));
    }

    public function testGetMonthTotalWorkedHoursForCurrentMonth()
    {
        $user = new User;
        $this->date = new \DateTime("15 May 2014");
        $date = new \DateTime("15 May 2014");

        $this->bookingRepository
            ->expects($this->once())
            ->method('getMonthBookedToDateTotalByUser')
            ->with($user, $date)
            ->will($this->returnValue(40));

        $this->assertEquals(40, $this->getService()->getMonthTotalWorked($user, $date));
    }

    public function testGetWeekTotals()
    {
        $user = new User;
        $date = new \DateTime("15 May 2014");

        $week = ['firstDay' => new \DateTime("10 May 2014"), 'lastDay' => new \DateTime("17 May 2014")];
        $this->periodService
             ->expects($this->once())
             ->method('getFirstAndLastDayOfWeek')
             ->with($date)
             ->will($this->returnValue($week));

        $this->bookingRepository
             ->expects($this->once())
             ->method('getTotalBookedBetweenByUser')
             ->with($user, $week['firstDay'], $week['lastDay'])
             ->will($this->returnValue(10));

        $this->periodService
             ->expects($this->once())
             ->method('getNumWorkingDaysInWeek')
             ->with($date)
             ->will($this->returnValue(2));

        $ret = $this->getService()->getWeekTotals($user, $date);

        $expected = [
            'weekTotalWorkedHours'  => 10,
            'weekTotalHours'        => 2 * 7.5,
            'balance'               => -5
        ];
        $this->assertEquals($expected, $ret);
    }

    public function testGetMonthTotals()
    {
        $user = new User;
        $date = new \DateTime("15 May 2014");
        $this->date = new \DateTime("16 May 2014");

        $service = $this->getMock(
            'JhFlexiTime\Service\TimeCalculatorService',
            [
                'getMonthTotalWorked',
                'getMonthBalance',
                'getRunningBalance',
                'getBalanceForward',
            ],
            [
                $this->options,
                $this->bookingRepository,
                $this->balanceRepository,
                $this->periodService,
                $this->date
            ]
        );

        $service
            ->expects($this->once())
            ->method('getMonthTotalWorked')
            ->with($user, $date)
            ->will($this->returnValue(10));

        $this->periodService
            ->expects($this->once())
            ->method('getTotalHoursInMonth')
            ->with($date)
            ->will($this->returnValue(50));

        $service
            ->expects($this->once())
            ->method('getMonthBalance')
            ->with($user, $date)
            ->will($this->returnValue(10));

        $service
            ->expects($this->once())
            ->method('getRunningBalance')
            ->with($user)
            ->will($this->returnValue(5));

        $this->periodService
            ->expects($this->once())
            ->method('getRemainingHoursInMonth')
            ->with($this->date)
            ->will($this->returnValue(100));

        $service
            ->expects($this->once())
            ->method('getBalanceForward')
            ->with($user)
            ->will($this->returnValue(2.5));

        $expected = [
            'monthTotalWorkedHours' => 10,
            'monthTotalHours'       => 50,
            'monthBalance'          => 10,
            'runningBalance'        => 5,
            'monthRemainingHours'   => 100,
            'balanceForward'        => 2.5,
        ];

        $this->assertEquals($expected, $service->getTotals($user, $date));
    }
}
