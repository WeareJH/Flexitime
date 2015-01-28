<?php

namespace JhFlexiTimeTest\Repository\Factory;

use JhFlexiTime\Repository\Factory\CappedCreditRepositoryFactory;

/**
 * Class CappedCreditRepositoryFactoryTest
 * @package JhFlexiTimeTest\Repository\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditRepositoryFactoryTest extends \PHPUnit_Framework_TestCase
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
            ->with('JhFlexiTime\Entity\CappedCredit')
            ->will($this->returnValue($meta));

        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator
            ->expects($this->once())
            ->method('get')
            ->with('JhFlexiTime\ObjectManager')
            ->will($this->returnValue($objectManager));


        $factory = new CappedCreditRepositoryFactory();
        $this->assertInstanceOf('JhFlexiTime\Repository\CappedCreditRepository', $factory->createService($serviceLocator));
    }
}
