<?php

namespace JhFlexiTimeTest\Service;

use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Service\RunningBalanceService;
use JhUser\Entity\User;

class RunningBalanceServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $userRepository;
    protected $userSettingsRepository;
    protected $runningBalanceService;
    protected $periodService;
    protected $bookingRepository;
    protected $balanceRepository;
    protected $date;
    protected $objectManager;

    public function setUp()
    {
        $this->userRepository           = $this->getMock('JhUser\Repository\UserRepositoryInterface');
        $this->userSettingsRepository   = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');
        $this->balanceRepository        = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');
        $this->periodService            = $this->getMock('JhFlexiTime\Service\PeriodServiceInterface');
        $this->bookingRepository        = $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface');
        $this->objectManager            = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->date                     = new \DateTime("2 May 2014");

        $this->runningBalanceService = new RunningBalanceService(
            $this->userRepository,
            $this->userSettingsRepository,
            $this->bookingRepository,
            $this->balanceRepository ,
            $this->periodService,
            $this->objectManager,
            $this->date
        );
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param array $expectedMonths
     * @dataProvider monthRangeProvider
     */
    public function testGetMonths(\DateTime $start, \DateTime $end, array $expectedMonths)
    {
        $period = $this->runningBalanceService->getMonthsBetweenUserStartAndLastMonth($start, $end);
        $months = iterator_to_array($period);
        $this->assertEquals($months, $expectedMonths);
    }

    public function monthRangeProvider()
    {
        return [
            [new \DateTime("12 February 2014"), new \DateTime("12 May 2014"), [ new \DateTime('Feb 2014'), new \DateTime('Mar 2014'), new \DateTime('Apr 2014'), new \DateTime('May 2014')]],
            [new \DateTime("01 January 2014"), new \DateTime("12 May 2014"), [new \DateTime('Jan 2014'), new \DateTime('Feb 2014'), new \DateTime('Mar 2014'), new \DateTime('Apr 2014'), new \DateTime('May 2014')]],
            [new \DateTime("01 March 2014"), new \DateTime("1 May 2014 00:00:00"), [new \DateTime('Mar 2014'), new \DateTime('Apr 2014'), new \DateTime('May 2014')]],
        ];
    }

    /**
     * @param int $initalBalance
     * @param int $hoursInMonth
     * @param int $hoursWorked
     * @param int $expectedBalance
     * @dataProvider calculateMonthBalanceProvider
     */
    public function testCalculateMonthBalanceCorrectlyAddsBalance($initalBalance, $hoursInMonth, $hoursWorked, $expectedBalance)
    {
        $date           = new \DateTime("2 May 2014");
        $user           = new User();
        $runningBalance = new RunningBalance();
        $runningBalance->setBalance($initalBalance);

        $this->periodService
             ->expects($this->once())
             ->method('getTotalHoursInMonth')
             ->with($date)
             ->will($this->returnValue($hoursInMonth));

        $this->bookingRepository
             ->expects($this->once())
             ->method('getMonthBookedTotalByUser')
             ->with($user, $date)
             ->will($this->returnValue($hoursWorked));

        $this->runningBalanceService->calculateMonthBalance($user, $runningBalance, $date);
        $this->assertEquals($expectedBalance, $runningBalance->getBalance());
    }

    public function calculateMonthBalanceProvider()
    {
        return [
            [0,     50, 40, -10],
            [20,    50, 40, 10],
            [-20,   50, 70, 0],
            [-20,   50, 80, 10],
        ];
    }

    public function testRecalculateUserRunningBalance()
    {
        $user           = new User;
        $userStartDate  = new \DateTime("13 March 2014");
        $startBalance   = 10;

        $lastMonth = new \DateTime("1 April 2014");

        $period = new \DatePeriod(
            new \DateTime("1 March 2014"),
            new \DateInterval("P1M"),
            new \DateTime("1 May 2014")
        );

        $dates = iterator_to_array($period);
        $service = $this->getMock('JhFlexiTime\Service\RunningBalanceService', ['getMonthsBetweenUserStartAndLastMonth', 'calculateMonthBalance'],
            [
                $this->userRepository,
                $this->userSettingsRepository,
                $this->bookingRepository,
                $this->balanceRepository,
                $this->periodService,
                $this->objectManager,
                $this->date,
            ]
        );

        $service
             ->expects($this->once())
             ->method('getMonthsBetweenUserStartAndLastMonth')
             ->with($this->equalTo($userStartDate), $this->equalTo($lastMonth))
             ->will($this->returnValue($period));

        $runningBalance = new RunningBalance();


        $service
            ->expects($this->at(1))
            ->method('calculateMonthBalance')
            ->with($user, $runningBalance, $dates[0]);

        $service
            ->expects($this->at(2))
            ->method('calculateMonthBalance')
            ->with($user, $runningBalance, $dates[1]);

        $this->objectManager->expects($this->once())->method('flush');
        $service->recalculateRunningBalance($user, $runningBalance, $userStartDate, $startBalance);
    }

    public function testRecalculateIsCalledForEachUser()
    {

        $service = $this->getMock('JhFlexiTime\Service\RunningBalanceService', ['calculateMonthBalance'],
            [
                $this->userRepository,
                $this->userSettingsRepository,
                $this->bookingRepository,
                $this->balanceRepository,
                $this->periodService,
                $this->objectManager,
                $this->date,
            ]
        );

        $user1 = new User;
        $user2 = new User;
        $runningBalance1 = new RunningBalance;
        $runningBalance2 = new RunningBalance;

        $users = [$user1, $user2];

        $lastMonth = new \DateTime("1 April 2014");

        $this->userRepository
             ->expects($this->once())
             ->method('findAll')
             ->with(false)
             ->will($this->returnValue($users));

        $this->balanceRepository
              ->expects($this->at(0))
              ->method('findOneByUser')
              ->with($user1)
              ->will($this->returnValue($runningBalance1));

        $this->balanceRepository
            ->expects($this->at(1))
            ->method('findOneByUser')
            ->with($user2)
            ->will($this->returnValue($runningBalance2));

        $service->expects($this->at(0))
                ->method('calculateMonthBalance')
                ->with($user1, $runningBalance1, $lastMonth);

        $service->expects($this->at(1))
            ->method('calculateMonthBalance')
            ->with($user2, $runningBalance2, $lastMonth);

        $this->objectManager->expects($this->once())->method('flush');
        $service->calculatePreviousMonthBalance();
    }

    public function testRecalculateSingleUserRunningBalance()
    {
        $user = new User;
        $runningBalance = new RunningBalance;
        $userSettings = new UserSettings;
        $userSettings->setFlexStartDate(new \DateTime("12 March 2014"));

        $this->balanceRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->returnValue($runningBalance));

        $this->userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->With($user)
            ->will($this->returnValue($userSettings));

        $this->runningBalanceService->recalculateUserRunningBalance($user);
    }

    public function testRecalculateAllUsersRunningBalance()
    {
        $service = $this->getMock('JhFlexiTime\Service\RunningBalanceService', ['recalculateRunningBalance'],
            [
                $this->userRepository,
                $this->userSettingsRepository,
                $this->bookingRepository,
                $this->balanceRepository,
                $this->periodService,
                $this->objectManager,
                $this->date,
            ]
        );

        $user1 = new User;
        $user2 = new User;
        $runningBalance1 = new RunningBalance;
        $runningBalance2 = new RunningBalance;
        $userSettings1 = new UserSettings;
        $userSettings2 = new UserSettings;
        $userSettings1->setFlexStartDate(new \DateTime("12 March 2014"));
        $userSettings2->setFlexStartDate(new \DateTime("12 March 2014"));

        $users = [$user1, $user2];

        $this->userRepository
             ->expects($this->once())
             ->method('findAll')
             ->with(false)
             ->will($this->returnValue($users));

        $this->balanceRepository
            ->expects($this->at(0))
            ->method('findOneByUser')
            ->with($user1)
            ->will($this->returnValue($runningBalance1));

        $this->balanceRepository
            ->expects($this->at(1))
            ->method('findOneByUser')
            ->with($user2)
            ->will($this->returnValue($runningBalance2));

        $this->userSettingsRepository
             ->expects($this->at(0))
             ->method('findOneByUser')
             ->With($user1)
             ->will($this->returnValue($userSettings1));

        $this->userSettingsRepository
            ->expects($this->at(1))
            ->method('findOneByUser')
            ->With($user2)
            ->will($this->returnValue($userSettings2));

        $service->expects($this->at(0))
            ->method('recalculateRunningBalance')
            ->with($user1, $runningBalance1, $userSettings1->getFlexStartDate(), 0);

        $service->expects($this->at(1))
            ->method('recalculateRunningBalance')
            ->with($user2, $runningBalance2, $userSettings1->getFlexStartDate(), 0);

        $service->recalculateAllUsersRunningBalance();
    }

    public function testSetUserBalance()
    {
        $user = new User;
        $balance = 10;

        $userSettings = new UserSettings;
        $this->userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->returnValue($userSettings));

        $this->objectManager->expects($this->once())->method('flush');
        $this->runningBalanceService->setUserStartingBalance($user, $balance);
        $this->assertEquals(10, $userSettings->getStartingBalance());
    }
}
