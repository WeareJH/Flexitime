<?php

namespace JhFlexiTimeTest\Repository;

use JhFlexiTimeTest\Fixture\MultiUserCreditCaps;
use JhUser\Entity\User;
use JhFlexiTimeTest\Util\ServiceManagerFactory;

/**
 * Class CappedCreditRepositoryTest
 * @package JhFlexiTimeTest\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditRepositoryTest extends \PHPUnit_Framework_TestCase
{

    protected $objectManager;
    protected $objectRepository;
    protected $balanceRepository;
    protected $repository;
    protected $fixtureExecutor;
    protected $sl;

    public function setUp()
    {
        $this->objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->sl = ServiceManagerFactory::getServiceManager();
        $this->repository       = $this->sl->get('JhFlexiTime\Repository\CappedCreditRepository');
        $this->fixtureExecutor  = $this->sl->get('Doctrine\Common\DataFixtures\Executor\AbstractExecutor');
        $this->assertInstanceOf('JhFlexiTime\Repository\CappedCreditRepository', $this->repository);
    }

    public function testFindAllByUser()
    {
        $user = new User;
        $user->setEmail('aydin@hotmail.co.uk')->setPassword("password");
        $fixture = new MultiUserCreditCaps($user);
        $this->fixtureExecutor->execute([$fixture]);

        $records = $this->repository->findAllByUser($user);
        $this->assertSame(count($fixture->getUserRecords()), count($records));

        foreach ($records as $record) {
            $this->assertEquals($record->getUser()->getId(), $user->getId());
            $this->assertEquals($record->getUser()->getEmail(), $user->getEmail());
            $this->assertEquals($record->getCappedCredit(), 10);
        }
    }

    public function tesGetTotalCappedCreditByUser()
    {
        $user = new User;
        $user->setEmail('aydin@hotmail.co.uk')->setPassword("password");
        $fixture = new MultiUserCreditCaps($user);
        $this->fixtureExecutor->execute([$fixture]);

        $this->assertEquals(100, $this->repository->getTotalCappedCreditByUser($user));
    }

    public function testDeleteAllByUser()
    {
        $user = new User;
        $user->setEmail('aydin@hotmail.co.uk')->setPassword("password");
        $fixture = new MultiUserCreditCaps($user);
        $this->fixtureExecutor->execute([$fixture]);

        $this->repository->deleteAllByUser($user);

        $this->assertEquals(0, $this->repository->getTotalCappedCreditByUser($user));
        $this->assertCount(0, $this->repository->findAllByUser($user));
        $this->assertEquals(100, $this->repository->getTotalCappedCreditByUser($fixture->getUser2()));
        $this->assertCount(10, $this->repository->findAllByUser($fixture->getUser2()));
    }

    public function testDeleteAll()
    {
        $user = new User;
        $user->setEmail('aydin@hotmail.co.uk')->setPassword("password");
        $fixture = new MultiUserCreditCaps($user);
        $this->fixtureExecutor->execute([$fixture]);

        $this->repository->deleteAll();

        $this->assertEquals(0, $this->repository->getTotalCappedCreditByUser($user));
        $this->assertCount(0, $this->repository->findAllByUser($user));
        $this->assertEquals(0, $this->repository->getTotalCappedCreditByUser($fixture->getUser2()));
        $this->assertCount(0, $this->repository->findAllByUser($fixture->getUser2()));
    }
}
