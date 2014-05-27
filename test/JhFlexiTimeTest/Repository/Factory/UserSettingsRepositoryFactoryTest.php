<?php

namespace JhFlexiTimeTest\Repository\Factory;

use JhFlexiTime\Repository\Factory\UserSettingsRepositoryFactory;

/**
 * Class UserSettingsRepositoryFactoryTest
 * @package JhFlexiTimeTest\Repository\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class UserSettingsRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsRepositoryFromObjectManager()
    {
        $objectManager    = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('JhFlexiTime\Entity\UserSettings'))
            ->will($this->returnValue($objectRepository));

        $services = [
            'JhFlexiTime\ObjectManager' => $objectManager,
        ];

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($serviceName) use ($services) {
                        return $services[$serviceName];
                    }
                )
            );


        $factory = new UserSettingsRepositoryFactory();
        $this->assertInstanceOf(
            'JhFlexiTime\Repository\UserSettingsRepository',
            $factory->createService($serviceLocator)
        );
    }
}
