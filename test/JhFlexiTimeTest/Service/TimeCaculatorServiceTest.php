<?php

namespace JhFlexiTimeTest\Service;

use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Service\TimeCalculatorService;

/**
 * Class TimeCalculatorServiceTest
 * @package JhFlexiTimeTest\Service
 * @author Aydin Hassan <aydin@wearejh.com>
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

    /**
     * @var \JhFlexiTime\Service\PeriodService
     */
    protected $periodService;

    /**
     * Create Service
     */
    public function setUp()
    {
        $this->bookingRepository    = $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface');
        $this->balanceService       = $this->getMock('JhFlexiTime\Service\BalanceServiceInterface');
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
            $this->balanceService,
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
        $this->date     = new \DateTime;
        $mockUser       = $this->getMock('ZfcUser\Entity\UserInterface');
        $runningBalance = new RunningBalance();
        $runningBalance->setBalance($initialBalance);

        $this->balanceService->expects($this->once())
            ->method('getRunningBalance')
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
}
