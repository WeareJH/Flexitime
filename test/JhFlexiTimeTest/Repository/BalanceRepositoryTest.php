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

        $ret = $this->balanceRepository->findOneByUser($userMock);
        $this->assertSame($runningBalance, $ret);
    }
}
