<?php

namespace JhFlexiTimeTest\Repository;

use JhFlexiTimeTest\Fixture\MultiUserBookedCredit;
use JhUser\Entity\User;
use JhFlexiTimeTest\Util\ServiceManagerFactory;

/**
 * Class BookedCreditRepositoryTest
 * @package JhFlexiTimeTest\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookedCreditRepositoryTest extends \PHPUnit_Framework_TestCase
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
        $this->repository       = $this->sl->get('JhFlexiTime\Repository\BookedCreditRepository');
        $this->fixtureExecutor  = $this->sl->get('Doctrine\Common\DataFixtures\Executor\AbstractExecutor');
        $this->assertInstanceOf('JhFlexiTime\Repository\BookedCreditRepository', $this->repository);
    }

    public function testFindAllByUser()
    {
        $user = new User;
        $user->setEmail('aydin@hotmail.co.uk')->setPassword("password");
        $fixture = new MultiUserBookedCredit($user);
        $this->fixtureExecutor->execute([$fixture]);

        $records = $this->repository->findAllByUser($user);
        $this->assertSame(count($fixture->getUserRecords()), count($records));

        foreach ($records as $record) {
            $this->assertEquals($record->getUser()->getId(), $user->getId());
            $this->assertEquals($record->getUser()->getEmail(), $user->getEmail());
            $this->assertEquals($record->getAmount(), 10);
        }
    }

    public function testFindAllByUserPaginated()
    {
        $user = new User;
        $user->setEmail('aydin@hotmail.co.uk')->setPassword("password");
        $fixture = new MultiUserBookedCredit($user);
        $this->fixtureExecutor->execute([$fixture]);

        $records = $this->repository->findAllByUser($user, true);
        $this->assertSame($records->count(), count($records));

        $this->assertInstanceOf('Zend\Paginator\Paginator', $records);
        foreach ($records as $record) {
            $this->assertEquals($record->getUser()->getId(), $user->getId());
            $this->assertEquals($record->getUser()->getEmail(), $user->getEmail());
            $this->assertEquals($record->getAmount(), 10);
        }
    }
}
