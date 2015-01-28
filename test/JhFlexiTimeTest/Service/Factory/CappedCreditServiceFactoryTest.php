<?php

namespace JhFlexiTimeTest\Service\Factory;

use JhFlexiTime\Service\Factory\CappedCreditServiceFactory;
use PHPUnit_Framework_TestCase;

/**
 * Class CappedCreditServiceFactoryTest
 * @package JhFlexiTimeTest\Service\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditServiceFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services         = [
            'JhFlexiTime\ObjectManager'
                => $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            'JhFlexiTime\Repository\CappedCreditRepository'
                => $this->getMock('JhFlexiTime\Repository\CappedCreditRepositoryInterface'),
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

        $factory = new CappedCreditServiceFactory();
        $this->assertInstanceOf('JhFlexiTime\Service\CappedCreditService', $factory->createService($serviceLocator));
    }
}
