<?php

namespace JhFlexiTimeTest\Repository;

use JhUser\Entity\User;
use JhFlexiTimeTest\Util\ServiceManagerFactory;
use JhFlexiTimeTest\Fixture\SingleSettings;

/**
 * Class UserSettingsRepositoryTest
 * @package JhFlexiTimeTest\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserSettingsRepositoryTest extends \PHPUnit_Framework_TestCase
{

    protected $repository;
    protected $fixtureExecutor;

    public function setUp()
    {
        $sm = ServiceManagerFactory::getServiceManager();
        $this->repository       = $sm->get('JhFlexiTime\Repository\UserSettingsRepository');
        $this->fixtureExecutor  = $sm->get('Doctrine\Common\DataFixtures\Executor\AbstractExecutor');
        $this->assertInstanceOf('JhFlexiTime\Repository\UserSettingsRepository', $this->repository);
    }

    public function testFindOneByReturnsNullIfNotExists()
    {

        $this->assertNull($this->repository->findOneByUser(new User));
    }

    public function testFindOneByReturnsSettingsIfExists()
    {
        $settings = new SingleSettings();
        $this->fixtureExecutor->execute([$settings]);
        $result = $this->repository->findOneByUser($settings->getSettings()->getUser());
        $this->assertInstanceOf('JhFlexiTime\Entity\UserSettings', $result);
        $this->assertEquals($settings->getSettings()->getUser()->getEmail(), $result->getUser()->getEmail());
        $this->assertEquals($settings->getSettings()->getStartingBalance(), $result->getStartingBalance());
    }
}
