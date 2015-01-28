<?php

namespace JhFlexiTimeTest\Service;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Entity\CappedCredit;
use JhFlexiTime\Service\CappedCreditService;
use JhUser\Entity\User;
use PHPUnit_Framework_TestCase;

/**
 * Class CappedCreditServiceTest
 * @package JhFlexiTimeTest\Service
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CappedCreditService
     */
    protected $service;

    /**
     * @var \JhFlexiTime\Repository\CappedCreditRepositoryInterface
     */
    protected $repository;

    /**
     * @var
     */
    protected $objectManager;

    public function setUp()
    {
        $this->repository       = $this->getMock('JhFlexiTime\Repository\CappedCreditRepositoryInterface');
        $this->objectManager    = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->service          = new CappedCreditService($this->repository, $this->objectManager);
    }

    public function testCreate()
    {
        $this->objectManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('\JhFlexiTime\Entity\CappedCredit'));

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        $this->service->create(new User, 100, new DateTime('10 October 2014'));
    }

    public function testSave()
    {
        $this->objectManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('\JhFlexiTime\Entity\CappedCredit'));

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        $capped = new CappedCredit;
        $capped->setDate(new DateTime('10 October 2014'));
        $capped->setUser(new User);
        $capped->setCappedCredit(100);

        $this->service->save($capped);
    }

    public function testClearCappedCreditEntries()
    {
        $user = new User;

        $this->repository
            ->expects($this->once())
            ->method('deleteAllByUser')
            ->with($user);

        $this->service->clearCappedCreditEntries($user);
    }
}
