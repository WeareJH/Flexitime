<?php

namespace JhFlexiTimeTest\Service;

use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Service\BalanceService;
use JhFlexiTime\Entity\Booking;
use JhUser\Entity\User;

/**
 * Class BalanceServiceTest
 * @package JhFlexiTimeTest\Service
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BalanceServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        $options = new ModuleOptions(array('hours_in_day' => 7.5));
        return $options;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockPeriodService()
    {
        return $this
            ->getMockBuilder('JhFlexiTime\Service\PeriodService')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test that when a new total is set, the getRunningTotal function
     * returns the difference between the new balance and the old balance
     *
     * @dataProvider balanceUpdateProvider
     */
    public function testBalanceDiff($newTotal, $oldBalance, $expected, $newBalance)
    {
        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $balanceRepository  = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');
        $balanceService     = new BalanceService($this->getOptions(), $balanceRepository, $em, $this->getMockPeriodService());

        $booking = new Booking();
        $booking->setTotal($newTotal);
        $booking->setBalance($oldBalance);

        $runningBalance = $balanceService->getBalanceDiff($booking);
        
        $this->assertEquals($expected, $runningBalance[0]);
        $this->assertEquals($newBalance, $runningBalance[1]);
    }

    /**
     * @return array
     */
    public function balanceUpdateProvider()
    {
        /**
         *  New Total | Old Balance | Balance Diff | New Balance
         */
        return array(
            array(12,   0.5,    4,      4.5),
            array(7.5,  0,      0,      0),
            array(6.5,  0,      -1,     -1),
            array(6,    3,      -4.5,   -1.5),
            array(9.5,  -2,     4,      2),
        );
    }

    /**
     *
     * @dataProvider updateBookingProvider
     */
    public function testUpdateBookingSetsBalance($newTotal, $expRunningBalance)
    {
        $user = $this->getMock('ZfcUser\Entity\UserInterface');
        $booking = new Booking();
        $booking->setUser($user);
        $booking->setTotal($newTotal);
        $booking->setBalance(0);

        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(array('persist'))
            ->disableOriginalConstructor()
            ->getMock();

        $balanceRepository  = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');


        $balanceService = new BalanceService($this->getOptions(), $balanceRepository, $em, $this->getMockPeriodService());
        $balanceService->updateBalance($booking);

        $this->assertEquals($expRunningBalance, $booking->getBalance());
    }

    /**
     * @return array
     */
    public function updateBookingProvider()
    {
        /**
         *  New Total | Expected Running Balance
         */
        return array(
            array(-7.5, -15),
            array(15,   7.5),
            array(8,    0.5),
            array(6,    -1.5)
        );
    }

    /**
     * Test get running balance function
     */
    public function testGetRunningBalance()
    {
        $userMock = $this->getMock('ZfcUser\Entity\UserInterface');
        $runningBalance = new RunningBalance();

        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $balanceRepository  = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');
        $balanceRepository->expects($this->once())
            ->method('findByUser')
            ->with($userMock)
            ->will($this->returnValue($runningBalance));

        $balanceService = new BalanceService($this->getOptions(), $balanceRepository, $em, $this->getMockPeriodService());
        $ret = $balanceService->getRunningBalance($userMock);
        $this->assertSame($runningBalance, $ret);
    }

    /**
     * Test get running balance creates new Running Balance if it does not exist for the current user
     */
    public function testGetRunningBalanceReturnsNewInstanceIfNotExist()
    {
        $user = new User;

        $em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();


        $em
            ->expects($this->once())
            ->method('persist');

        $balanceRepository  = $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface');
        $balanceRepository->expects($this->once())
            ->method('findByUser')
            ->with($user)
            ->will($this->returnValue(null));

        $balanceService = new BalanceService($this->getOptions(), $balanceRepository, $em, $this->getMockPeriodService());
        $ret = $balanceService->getRunningBalance($user);
        $this->assertInstanceOf('JhFlexiTime\Entity\RunningBalance', $ret);
        $this->assertEquals(0, $ret->getBalance());
        $this->assertSame($user, $ret->getUser());
    }
}
