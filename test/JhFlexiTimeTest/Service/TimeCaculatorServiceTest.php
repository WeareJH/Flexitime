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
     * @var \JhFlexiTime\Repository\BalanceRepositoryInterface
     */
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
        $this->bookingRepository     = $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface');
        $this->balanceRepository  = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');
        $this->periodService      = $this->getMockBuilder('JhFlexiTime\Service\PeriodService')
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
     * @param float $monthRemainingHours
     * @param float $bookedAfter
     *
     * @dataProvider runningBalanceProvider
     */
    public function testGetRunningBalance($initialBalance, $expectedBalance, $monthRemainingHours, $bookedAfter)
    {
        $this->date     = new \DateTime;
        $mockUser       = $this->getMock('ZfcUser\Entity\UserInterface');
        $runningBalance = new RunningBalance();
        $runningBalance->setBalance($initialBalance);

        $this->balanceRepository->expects($this->once())
            ->method('findByUser')
            ->with($mockUser)
            ->will($this->returnValue($runningBalance));

        $this->periodService->expects($this->once())
            ->method('getRemainingHoursInMonth')
            ->with($this->date)
            ->will($this->returnValue($monthRemainingHours));

        $this->bookingRepository->expects($this->once())
             ->method('getTotalBookedAfter')
             ->with($mockUser, $this->date)
             ->will($this->returnValue($bookedAfter));

        $balance = $this->getService()->getRunningBalance($mockUser);
        $this->assertEquals($expectedBalance, $balance);
    }

    /**
     * @return array
     */
    public function runningBalanceProvider()
    {
        /**
         * Initial Balance | Expected Balance | Month Remaining | Booked After
         */
        return array(
            array(0,        120,    112.50, 0),
            array(5,        125,    112.50, 0),
            array(45,       125,    112.50, 40),
            array(50,       170,    112.50, 0),
            array(-120,     0,      112.50, 0),
            array(-100,     0,      112.50, 20),
            array(-100,     -10,    112.50, 30),
            array(-120,     10,     112.50, -10),
            array(-120,     10,     112.50, -10),
            array(-120,     -112.5, 0,      0),
            array(-120,     -100,   0,      -12.5),
            array(0,        7.5,    0,      -0),
            array(0,        -7.5,   0,      15),
            array(-307.5,   -202.5, 97.50, 0),
            array(-119.1,   0.9,    112.5,    0),
        );
    }
}
