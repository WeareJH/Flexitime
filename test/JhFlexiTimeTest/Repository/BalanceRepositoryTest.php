<?php

namespace JhFlexiTimeTest\Repository;


use JhFlexiTime\Repository\BalanceRepository;
use JhFlexiTime\Entity\RunningBalance;
use JhUser\Entity\User;

class BalanceRepositoryTest extends \PHPUnit_Framework_TestCase
{

    protected $objectManager;
    protected $objectRepository;
    protected $balanceRepository;

    public function setUp()
    {
        $this->objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * Test get running balance function
     */
    public function testGetRunningBalance()
    {
        $this->balanceRepository = new BalanceRepository(
            $this->objectRepository,
            $this->objectManager
        );

        $userMock = $this->getMock('ZfcUser\Entity\UserInterface');
        $runningBalance = new RunningBalance();

        $this->objectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $userMock])
            ->will($this->returnValue($runningBalance));

        $ret = $this->balanceRepository->findByUser($userMock);
        $this->assertSame($runningBalance, $ret);
    }

    /**
     * Test get running balance creates new Running Balance if it does not exist for the current user
     */
    public function testGetRunningBalanceReturnsNewInstanceIfNotExist()
    {

        $this->balanceRepository = $this->getMock(
            'JhFlexiTime\Repository\BalanceRepository',
            ['createRunningBalance'],
            [$this->objectRepository, $this->objectManager]
        );

        $userMock = $this->getMock('ZfcUser\Entity\UserInterface');
        $runningBalance = new RunningBalance();

        $this->balanceRepository
            ->expects($this->once())
            ->method('createRunningBalance')
            ->with($userMock)
            ->will($this->returnValue($runningBalance));

        $this->objectRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $userMock])
            ->will($this->returnValue(null));

        $ret = $this->balanceRepository->findByUser($userMock);
        $this->assertSame($runningBalance, $ret);
    }

    public function testCreateRunningBalance()
    {
        $this->balanceRepository = new BalanceRepository(
            $this->objectRepository,
            $this->objectManager
        );

        $this->objectManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('JhFlexiTime\Entity\RunningBalance'));

        $this->objectManager
             ->expects($this->once())
             ->method('flush');

        $user = new User;
        $ret = $this->balanceRepository->createRunningBalance($user);
        $this->assertInstanceOf('JhFlexiTime\Entity\RunningBalance', $ret);
        $this->assertSame($user, $ret->getUser());
        $this->assertEquals(0, $ret->getBalance());
    }

} 