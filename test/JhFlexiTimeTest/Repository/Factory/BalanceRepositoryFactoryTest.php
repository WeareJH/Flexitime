<?php

namespace JhFlexiTimeTest\Repository\Factory;

use JhFlexiTime\Repository\Factory\BalanceRepositoryFactory;

/**
 * Class BalanceRepositoryFactoryTest
 * @package JhFlexiTimeTest\Repository\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BalanceRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsRepositoryFromObjectManager()
    {
        $objectManager    = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $objectManager
            ->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('JhFlexiTime\Entity\RunningBalance'))
            ->will($this->returnValue($objectRepository));
        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('JhFlexiTime\ObjectManager')
            ->will($this->returnValue($objectManager));

        $factory = new BalanceRepositoryFactory();

        $this->assertInstanceOf('JhFlexiTime\Repository\BalanceRepository', $factory->createService($serviceLocator));
    }
}
