<?php

namespace JhFlexiTimeTest\Listener;

use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Listener\BookingSaveListener;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Entity\RunningBalance;
use ZfcUser\Entity\UserInterface;
use Zend\EventManager\Event;
use JhFlexiTime\Entity\Booking;
use Zend\I18n\Validator\DateTime;

/**
 * Class BookingSaveListenerTest
 * @package JhFlexiTimeTest\Listener
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingSaveListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $bookingSaveListener;
    protected $balanceRepository;
    protected $objectManager;
    protected $userSettingsRepository;

    public function setUp()
    {

        $this->balanceRepository        = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');
        $this->objectManager            = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->userSettingsRepository   = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');

        $this->bookingSaveListener  = new BookingSaveListener(
            $this->objectManager,
            $this->balanceRepository,
            new \DateTime("12 April 2014"),
            new ModuleOptions(),
            $this->userSettingsRepository
        );
    }

    /**
     * @param $newTotal
     * @param $expBalance
     * @dataProvider balanceUpdateProvider
     */
    public function testBalanceIsUpdatedFromEvent($newTotal, $expBalance)
    {
        $user = $this->getMock('ZfcUser\Entity\UserInterface');
        $booking = new Booking();
        $booking->setUser($user);
        $booking->setTotal($newTotal);
        $booking->setBalance(0);

        $event = new Event();
        $event->setParam('booking', $booking);

        $this->bookingSaveListener->updateBalance($event);
        $this->assertEquals($expBalance, $booking->getBalance());
    }

    /**
     * @return array
     */
    public function balanceUpdateProvider()
    {
        /**
         *  New Total | Expected Running Balance
         */
        return [
            [-7.5, -15],
            [15,   7.5],
            [8,    0.5],
            [6,    -1.5]
        ];
    }

    public function testBalanceIsUpdateIfBookingIsInPreviousMonth()
    {
        $user = $this->getMock('ZfcUser\Entity\UserInterface');
        $booking = new Booking();
        $booking->setUser($user);
        $booking->setTotal(15);
        $booking->setBalance(0);
        $booking->setDate(new \DateTime("12 March 2014"));

        $event = new Event();
        $event->setParam('booking', $booking);

        $date = new \DateTime("12 April 2014");

        $bookingSaveListener = $this->getMock(
            'JhFlexiTime\Listener\BookingSaveListener',
            [
                'isDateInPreviousMonth',
                'updateRunningBalance',
                'getRunningBalance',
                'isDateAfterUsersStartTrackingMonth'],
            [
                $this->objectManager,
                $this->balanceRepository,
                $date,
                new ModuleOptions(),
                $this->userSettingsRepository
            ]
        );

        $bookingSaveListener
            ->expects($this->once())
            ->method('isDateAfterUsersStartTrackingMonth')
            ->with($booking)
            ->will($this->returnValue(true));

        $bookingSaveListener
            ->expects($this->once())
            ->method('isDateInPreviousMonth')
            ->with($booking->getDate(), $date)
            ->will($this->returnValue(true));

        $runningBalance = new RunningBalance();
        $bookingSaveListener
            ->expects($this->once())
            ->method('getRunningBalance')
            ->with($booking->getUser())
            ->will($this->returnValue($runningBalance));

        $bookingSaveListener
            ->expects($this->once())
            ->method('updateRunningBalance')
            ->with($booking, $runningBalance);

        $bookingSaveListener->updateBalance($event);
    }

    /**
     * @dataProvider updateRunningBalanceProvider
     */
    public function testUpdateRunningBalanceAddsBalanceDiff($total, $initialRunningBalance, $expectedRunningBalance)
    {
        $booking = new Booking;
        $booking->setTotal($total);
        $booking->setBalance(0);

        $runningBalance = new RunningBalance();
        $runningBalance->setBalance($initialRunningBalance);

        $this->bookingSaveListener->updateRunningBalance($booking, $runningBalance);
        $this->assertEquals($expectedRunningBalance, $runningBalance->getBalance());
    }

    public function updateRunningBalanceProvider()
    {
        /**
         *  New Total | Initial Running Balance | New Running  Balance
         */
        return [
            [7.5    ,2,     2],
            [15     ,2,     9.5],
            [7.5    ,0,     0],
            [0      ,0,     -7.5],
            [0      ,-1,    -8.5],
            [-5     ,-1,    -13.5],
        ];
    }

    /**
     * @param \DateTime $a
     * @param \DateTime $b
     * @param bool $expected
     * @dataProvider isDateInPreviousMonthProvider
     */
    public function testIsDateInPreviousMonth(\DateTime $a, \DateTime $b, $expected)
    {
        $result = $this->bookingSaveListener->isDateInPreviousMonth($a, $b);
        $this->assertEquals($result, $expected);
    }

    public function isDateInPreviousMonthProvider()
    {
        return [
            [new \DateTime("12 April 2014"),            new \DateTime("12 May 2014 23:59:59"),    true],
            [new \DateTime("30 April 2014 23:59:59"),   new \DateTime("12 May 2014 23:59:59"),    true],
            [new \DateTime("30 April 2014 23:59:59"),   new \DateTime("1 May 2014 00:00:00"),     true],
            [new \DateTime("12 April 2014"),            new \DateTime("12 March 2014 23:59:59"),  false],
            [new \DateTime("1 April 2014 00:00:00"),    new \DateTime("31 March 2014 23:59:59"),  false],
        ];
    }

    /**
     * Test get running balance function
     */
    public function testGetRunningBalance()
    {
        $userMock = $this->getMock('ZfcUser\Entity\UserInterface');
        $runningBalance = new RunningBalance();

        $this->balanceRepository->expects($this->once())
            ->method('findOneByUser')
            ->with($userMock)
            ->will($this->returnValue($runningBalance));

        $ret = $this->bookingSaveListener->getRunningBalance($userMock);
        $this->assertSame($runningBalance, $ret);
    }

    public function testAttach()
    {
        $eventManager   = $this->getMock('Zend\EventManager\EventManagerInterface');
        $sharedManager  = $this->getMock('Zend\EventManager\SharedEventManagerInterface');

        $eventManager
            ->expects($this->once())
            ->method('getSharedManager')
            ->will($this->returnValue($sharedManager));

        $return = [
            ['JhFlexiTime\Service\BookingService', 'create.pre', [$this->bookingSaveListener, 'updateBalance'], 100],
            ['JhFlexiTime\Service\BookingService', 'update.pre', [$this->bookingSaveListener, 'updateBalance'], 100],
            ['JhFlexiTime\Service\BookingService', 'delete.pre', [$this->bookingSaveListener, 'updateBalance'], 100],
        ];

        $sharedManager
            ->expects($this->exactly(3))
            ->method('attach')
            ->will($this->returnValueMap($return));

        $this->bookingSaveListener->attach($eventManager);
    }

    /**
     * @dataProvider startTrackingDateProvider
     */
    public function testIsDateAfterStartTrackingDate($startTrackingDate, $expected)
    {
        $user = $this->getMock('ZfcUser\Entity\UserInterface');

        $booking = new Booking();
        $booking->setUser($user);
        $booking->setDate(new \DateTime("30 March 2014"));

        $settings = new UserSettings();
        $settings->setFlexStartDate($startTrackingDate);

        $this->userSettingsRepository
             ->expects($this->once())
             ->method('findOneByUser')
             ->with($user)
             ->will($this->returnValue($settings));

        $this->assertEquals($expected, $this->bookingSaveListener->isDateAfterUsersStartTrackingMonth($booking));
    }

    public function startTrackingDateProvider()
    {
        return [
            [new \DateTime("1 April 2014"), false],
            [new \DateTime("10 June 2014"), false],
            [new \DateTime("31 March 2014"), true],
            [new \DateTime("1 March 2014"), true],
            [new \DateTime("1 February 2014"), true],
        ];
    }
}
