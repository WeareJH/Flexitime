<?php

namespace JhFlexiTimeTest\Service;

use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Repository\BalanceRepositoryInterface;
use JhFlexiTime\Repository\BookingRepository;
use JhFlexiTime\Repository\UserSettingsRepositoryInterface;
use JhFlexiTime\Service\PeriodService;
use JhFlexiTime\Service\RunningBalanceService;
use JhUser\Entity\User;
use JhFlexiTime\DateTime\DateTime;
use JhUser\Repository\UserRepositoryInterface;

/**
 * Class RunningBalanceServiceTest
 * @package JhFlexiTimeTest\Service
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunningBalanceServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var UserSettingsRepositoryInterface
     */
    protected $userSettingsRepository;

    /**
     * @var RunningBalanceService
     */
    protected $runningBalanceService;

    /**
     * @var PeriodService
     */
    protected $periodService;

    /**
     * @var BookingRepository
     */
    protected $bookingRepository;

    /**
     * @var BalanceRepositoryInterface
     */
    protected $balanceRepository;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Zend\EventManager\EventManagerInterface
     */
    protected $evm;

    public function setUp()
    {
        $this->userRepository           = $this->getMock('JhUser\Repository\UserRepositoryInterface');
        $this->userSettingsRepository   = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');
        $this->balanceRepository        = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');
        $this->periodService            = new PeriodService(new ModuleOptions());
        $this->bookingRepository        = $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface');
        $this->objectManager            = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->date                     = new DateTime("2 May 2014");
        $this->evm                      = $this->getMock('Zend\EventManager\EventManagerInterface');

        $this->runningBalanceService = new RunningBalanceService(
            $this->userRepository,
            $this->userSettingsRepository,
            $this->bookingRepository,
            $this->balanceRepository,
            $this->periodService,
            $this->objectManager,
            $this->date
        );

        $this->runningBalanceService->setEventManager($this->evm);
    }

    public function testCalculatePreviousMonthBalanceWithNoBookedHours()
    {
        list($user1, $userSettings1) = $this->getUser(new DateTime("10 April 2014"));
        list($user2, $userSettings2) = $this->getUser(new DateTime("1 January 2014"));

        $runningBalance1 = new RunningBalance;
        $runningBalance1->setBalance(20);

        $runningBalance2 = new RunningBalance;
        $runningBalance2->setBalance(-20);

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(false)
            ->will($this->returnValue([$user1, $user2]));

        $this->balanceRepository
            ->expects($this->exactly(2))
            ->method('findOneByUser')
            ->will($this->returnValueMap(
                array(
                    array($user1, $runningBalance1),
                    array($user2, $runningBalance2),
                )
            ));

        $this->userSettingsRepository
            ->expects($this->exactly(2))
            ->method('findOneByUser')
            ->will($this->returnValueMap(
                array(
                    array($user1, $userSettings1),
                    array($user2, $userSettings2),
                )
            ));

        $this->evmExpects([
            ['balance'   => $runningBalance1, 'count' => 1],
            ['balance'   => $runningBalance2, 'count' => 1]
        ]);

        $this->objectManager->expects($this->once())->method('flush');

        $this->runningBalanceService->indexPreviousMonthBalance();
        $this->assertEquals(-92.5, $runningBalance1->getBalance());
        $this->assertEquals(-185, $runningBalance2->getBalance());
    }

    public function testCalculatePreviousMonthBalanceWithBookedHours()
    {
        list($user1, $userSettings1) = $this->getUser(new DateTime("10 April 2014"));
        list($user2, $userSettings2) = $this->getUser(new DateTime("1 January 2014"));

        $runningBalance1 = new RunningBalance;
        $runningBalance1->setBalance(20);

        $runningBalance2 = new RunningBalance;
        $runningBalance2->setBalance(-20);

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(false)
            ->will($this->returnValue([$user1, $user2]));

        $this->balanceRepository
            ->expects($this->exactly(2))
            ->method('findOneByUser')
            ->will($this->returnValueMap(
                array(
                    array($user1, $runningBalance1),
                    array($user2, $runningBalance2),
                )
            ));

        $this->userSettingsRepository
            ->expects($this->exactly(2))
            ->method('findOneByUser')
            ->will($this->returnValueMap(
                array(
                    array($user1, $userSettings1),
                    array($user2, $userSettings2),
                )
            ));

        $end   = new DateTime("30 April 2014 23:59:59");
        $this->bookingRepository
            ->expects($this->at(0))
            ->method('getTotalBookedBetweenByUser')
            ->with($user1, $this->equalTo(new DateTime("10 April 2014 00:00:00")), $this->equalTo($end))
            ->will($this->returnValue(100));

        $this->bookingRepository
            ->expects($this->at(1))
            ->method('getTotalBookedBetweenByUser')
            ->with($user1, $this->equalTo(new DateTime("1 April 2014 00:00:00")), $this->equalTo($end))
            ->will($this->returnValue(100));

        $this->evmExpects([
            ['balance'   => $runningBalance1, 'count' => 1],
            ['balance'   => $runningBalance2, 'count' => 1]
        ]);
        $this->objectManager->expects($this->once())->method('flush');

        $this->runningBalanceService->indexPreviousMonthBalance();
        $this->assertEquals(7.5, $runningBalance1->getBalance());
        $this->assertEquals(-85, $runningBalance2->getBalance());
    }

    public function testCalculateRecalculateAllUsersRunningBalance()
    {
        $user1Start = new DateTime("10 April 2014");
        $user2Start = new DateTime("1 January 2014");
        list($user1, $userSettings1) = $this->getUser($user1Start);
        list($user2, $userSettings2) = $this->getUser($user2Start);

        $runningBalance1 = new RunningBalance;
        $runningBalance2 = new RunningBalance;

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(false)
            ->will($this->returnValue([$user1, $user2]));

        $this->balanceRepository
            ->expects($this->exactly(2))
            ->method('findOneByUser')
            ->will($this->returnValueMap(
                array(
                    array($user1, $runningBalance1),
                    array($user2, $runningBalance2),
                )
            ));

        $this->userSettingsRepository
            ->expects($this->exactly(2))
            ->method('findOneByUser')
            ->will($this->returnValueMap(
                array(
                    array($user1, $userSettings1),
                    array($user2, $userSettings2),
                )
            ));

        $this->evmExpects([
            ['balance'   => $runningBalance1, 'count' => 1],
            ['balance'   => $runningBalance2, 'count' => 4]
        ], true);

        $this->objectManager->expects($this->once())->method('flush');

        $end   = new DateTime("30 April 2014 23:59:59");
        $this->bookingRepository
            ->expects($this->at(0))
            ->method('getTotalBookedBetweenByUser')
            ->with($user1, $user1Start, $this->equalTo($end))
            ->will($this->returnValue(0));

        $this->bookingRepository
            ->expects($this->at(1))
            ->method('getTotalBookedBetweenByUser')
            ->with($user1, $user2Start, $this->equalTo(new DateTime('31 January 2014 23:59:59')))
            ->will($this->returnValue(0));

        $this->bookingRepository
            ->expects($this->at(2))
            ->method('getTotalBookedBetweenByUser')
            ->with(
                $user1,
                $this->equalTo(new DateTime('1 February 2014 00:00:00')),
                $this->equalTo(new DateTime('28 February 2014 23:59:59'))
            )
            ->will($this->returnValue(0));

        $this->bookingRepository
            ->expects($this->at(3))
            ->method('getTotalBookedBetweenByUser')
            ->with(
                $user1,
                $this->equalTo(new DateTime('1 March 2014 00:00:00')),
                $this->equalTo(new DateTime('31 March 2014 23:59:59'))
            )
            ->will($this->returnValue(0));

        $this->bookingRepository
            ->expects($this->at(4))
            ->method('getTotalBookedBetweenByUser')
            ->with(
                $user1,
                $this->equalTo(new DateTime('1 April 2014 00:00:00')),
                $this->equalTo(new DateTime('30 April 2014 23:59:59'))
            )
            ->will($this->returnValue(0));

        $this->runningBalanceService->reIndexAllUsersRunningBalance();
        $this->assertEquals(-112.5, $runningBalance1->getBalance());
        $this->assertEquals(-645, $runningBalance2->getBalance());
    }

    public function testCalculateRecalculateAllUsersRunningBalanceWithBookedHours()
    {
        $user1Start = new DateTime("10 April 2014");
        $user2Start = new DateTime("1 January 2014");
        list($user1, $userSettings1) = $this->getUser($user1Start);
        list($user2, $userSettings2) = $this->getUser($user2Start);

        $runningBalance1 = new RunningBalance;
        $runningBalance2 = new RunningBalance;

        $this->userRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(false)
            ->will($this->returnValue([$user1, $user2]));

        $this->balanceRepository
            ->expects($this->exactly(2))
            ->method('findOneByUser')
            ->will($this->returnValueMap(
                array(
                    array($user1, $runningBalance1),
                    array($user2, $runningBalance2),
                )
            ));

        $this->userSettingsRepository
            ->expects($this->exactly(2))
            ->method('findOneByUser')
            ->will($this->returnValueMap(
                array(
                    array($user1, $userSettings1),
                    array($user2, $userSettings2),
                )
            ));

        $this->evmExpects([
            ['balance'   => $runningBalance1, 'count' => 1],
            ['balance'   => $runningBalance2, 'count' => 4]
        ], true);

        $this->objectManager->expects($this->once())->method('flush');

        $end   = new DateTime("30 April 2014 23:59:59");
        $this->bookingRepository
            ->expects($this->at(0))
            ->method('getTotalBookedBetweenByUser')
            ->with($user1, $user1Start, $this->equalTo($end))
            ->will($this->returnValue(100));

        $this->bookingRepository
            ->expects($this->at(1))
            ->method('getTotalBookedBetweenByUser')
            ->with($user1, $user2Start, $this->equalTo(new DateTime('31 January 2014 23:59:59')))
            ->will($this->returnValue(50));

        $this->bookingRepository
            ->expects($this->at(2))
            ->method('getTotalBookedBetweenByUser')
            ->with(
                $user1,
                $this->equalTo(new DateTime('1 February 2014 00:00:00')),
                $this->equalTo(new DateTime('28 February 2014 23:59:59'))
            )
            ->will($this->returnValue(50));

        $this->bookingRepository
            ->expects($this->at(3))
            ->method('getTotalBookedBetweenByUser')
            ->with(
                $user1,
                $this->equalTo(new DateTime('1 March 2014 00:00:00')),
                $this->equalTo(new DateTime('31 March 2014 23:59:59'))
            )
            ->will($this->returnValue(50));

        $this->bookingRepository
            ->expects($this->at(4))
            ->method('getTotalBookedBetweenByUser')
            ->with(
                $user1,
                $this->equalTo(new DateTime('1 April 2014 00:00:00')),
                $this->equalTo(new DateTime('30 April 2014 23:59:59'))
            )
            ->will($this->returnValue(50));

        $this->runningBalanceService->reIndexAllUsersRunningBalance();
        $this->assertEquals(-12.5, $runningBalance1->getBalance());
        $this->assertEquals(-445, $runningBalance2->getBalance());
    }

    /**
     * @param float $startBalance
     * @param DateTime $startDate
     * @param float $expectedBalance
     * @param float $bookedHours
     * @dataProvider singleUserStartDateProvider
     */
    public function testRecalculateSingleUserRunningBalanceConsidersUsersStartDate(
        $startBalance,
        DateTime $startDate,
        $expectedBalance,
        $bookedHours
    ) {
        $user = new User;
        $runningBalance = new RunningBalance;
        $userSettings = new UserSettings;
        $userSettings->setFlexStartDate($startDate);
        $userSettings->setStartingBalance($startBalance);

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

        $this->evmExpects([['balance'   => $runningBalance, 'count' => 2]], true);
        $this->objectManager->expects($this->once())->method('flush');

        $this->bookingRepository
            ->expects($this->at(0))
            ->method('getTotalBookedBetweenByUser')
            ->with(
                $user,
                $startDate,
                $this->equalTo(new DateTime("31 March 2014 23:59:59"))
            )
            ->will($this->returnValue($bookedHours));

        $this->bookingRepository
            ->expects($this->at(1))
            ->method('getTotalBookedBetweenByUser')
            ->with(
                $user,
                $this->equalTo(new DateTime("01 April 2014 00:00:00")),
                $this->equalTo(new DateTime("30 April 2014 23:59:59"))
            )
            ->will($this->returnValue($bookedHours));

        $this->runningBalanceService->reIndexIndividualUserRunningBalance($user);
        $this->assertEquals($expectedBalance, $runningBalance->getBalance());
    }

    /**
     * @return array
     */
    public function singleUserStartDateProvider()
    {
        return [
            [
                'startingBalance'   => 0,
                'startDate'         => new DateTime("12-03-2014"),
                'expectedBalance'   => -270,
                'bookedHours'       => 0
            ],
            [
                'startingBalance'   => 10,
                'startDate'         => new DateTime("12-03-2014"),
                'expectedBalance'   => -260,
                'bookedHours'       => 0
            ],
        ];
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

    /**
     * @param DateTime $userStartDate
     * @return array
     */
    private function getUser(DateTime $userStartDate)
    {
        $user = new User;
        $userSettings = new UserSettings;
        $userSettings->setFlexStartDate($userStartDate);
        return [$user, $userSettings];
    }

    /**
     * @param array $balances
     * @param bool $addReindexPreAndPostEvents
     */
    private function evmExpects(array $balances, $addReindexPreAndPostEvents = false)
    {
        $invocationCount = 0;
        foreach ($balances as $balance) {

            if ($addReindexPreAndPostEvents) {
                $this->evm
                    ->expects($this->at($invocationCount++))
                    ->method('trigger')
                    ->with(
                        'reIndexUserRunningBalance.pre',
                        null,
                        ['runningBalance' => $balance['balance']]
                    );
            }

            for ($i = 0; $i < $balance['count']; $i++) {
                $this->evm
                    ->expects($this->at($invocationCount++))
                    ->method('trigger')
                    ->with(
                        'addMonthBalance.pre',
                        null,
                        ['runningBalance' => $balance['balance']]
                    );

                $this->evm
                    ->expects($this->at($invocationCount++))
                    ->method('trigger')
                    ->with(
                        'addMonthBalance.post',
                        null,
                        ['runningBalance' => $balance['balance']]
                    );
            }

            if ($addReindexPreAndPostEvents) {
                $this->evm
                    ->expects($this->at($invocationCount++))
                    ->method('trigger')
                    ->with(
                        'reIndexUserRunningBalance.post',
                        null,
                        ['runningBalance' => $balance['balance']]
                    );
            }
        }
    }
}
