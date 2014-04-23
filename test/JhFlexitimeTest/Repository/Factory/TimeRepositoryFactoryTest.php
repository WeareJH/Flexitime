<?php

namespace JhFlexiTimeTest\Repository\Factory;

use JhFlexiTime\Repository\Factory\BookingRepositoryFactory;

/**
 * Class BookingRepositoryFactoryTest
 * @package JhFlexiTimeTest\Repository\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsRepositoryFromObjectManager()
    {
        $objectManager    = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('JhFlexiTime\Entity\Booking'))
            ->will($this->returnValue($objectRepository));
        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('JhFlexiTime\ObjectManager')
            ->will($this->returnValue($objectManager));

        $factory = new BookingRepositoryFactory();

        $this->assertInstanceOf('JhFlexiTime\Repository\BookingRepository', $factory->createService($serviceLocator));
    }
}
