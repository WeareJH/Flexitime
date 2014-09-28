<?php

namespace JhFlexiTimeTest\Repository;

use JhFlexiTime\Repository\BalanceRepository;
use JhFlexiTime\Entity\RunningBalance;
use JhUser\Entity\User;
use JhFlexiTimeTest\Util\ServiceManagerFactory;
use JhFlexiTimeTest\Fixture\SingleRunningBalance;

/**
 * Class BalanceRepositoryTest
 * @package JhFlexiTimeTest\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BalanceRepositoryTest extends \PHPUnit_Framework_TestCase
{

    protected $objectManager;
    protected $objectRepository;
    protected $balanceRepository;
    protected $repository;
    protected $fixtureExecutor;

    public function setUp()
    {
        $this->objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $sm = ServiceManagerFactory::getServiceManager();
        $this->repository       = $sm->get('JhFlexiTime\Repository\BalanceRepository');
        $this->fixtureExecutor  = $sm->get('Doctrine\Common\DataFixtures\Executor\AbstractExecutor');
        $this->assertInstanceOf('JhFlexiTime\Repository\BalanceRepository', $this->repository);
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

    public function testFindOneByReturnsNullIfNotExists()
    {
        $this->assertNull($this->repository->findOneBy(["user" => 1]));
    }

    public function testFindOneByReturnsBalanceIfExists()
    {
        $balance = new SingleRunningBalance();
        $this->fixtureExecutor->execute([$balance]);
        $result = $this->repository->findOneBy(["user" => $balance->getBalance()->getUser()->getId()]);
        $this->assertInstanceOf('JhFlexiTime\Entity\RunningBalance', $result);
        $this->assertSame($balance->getBalance()->getUser()->getEmail(), $result->getUser()->getEmail());
        $this->assertSame($balance->getBalance()->getBalance(), $result->getBalance());
        $this->assertSame($balance->getBalance()->getId(), $result->getId());
    }
}
