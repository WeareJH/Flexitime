<?php

namespace JhFlexiTimeTest\Repository\Factory;

use JhFlexiTime\Repository\Factory\BookedCreditRepositoryFactory;

/**
 * Class BookedCreditRepositoryFactoryTest
 * @package JhFlexiTimeTest\Repository\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookedCreditRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsRepositoryFromObjectManager()
    {
        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $meta = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with('JhFlexiTime\Entity\BookedCredit')
            ->will($this->returnValue($meta));

        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator
            ->expects($this->once())
            ->method('get')
            ->with('JhFlexiTime\ObjectManager')
            ->will($this->returnValue($objectManager));


        $factory = new BookedCreditRepositoryFactory();
        $this->assertInstanceOf('JhFlexiTime\Repository\BookedCreditRepository', $factory->createService($serviceLocator));
    }
}
