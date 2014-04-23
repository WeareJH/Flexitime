<?php

namespace JhFlexiTimeTest\Service\Factory;

use JhFlexiTime\Service\Factory\PeriodServiceFactory;

/**
 * Class PeriodServiceFactoryTest
 * @package JhFlexiTimeTest\Service\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class PeriodServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services         = array(
            'FlexiOptions' => $this->getMock('JhFlexiTime\Options\ModuleOptions'),
        );

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

        $factory = new PeriodServiceFactory();
        $this->assertInstanceOf('JhFlexiTime\Service\PeriodService', $factory->createService($serviceLocator));
    }
}
