<?php

namespace JhFlexiTimeTest\Service\Factory;

use JhFlexiTime\Service\Factory\BalanceServiceFactory;

/**
 * Class BalanceServiceFactoryTest
 * @package JhFlexiTimeTest\Service\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BalanceServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services         = array(
            'FlexiOptions'                          => $this->getMock('JhFlexiTime\Options\ModuleOptions'),
            'JhFlexiTime\Repository\BalanceRepository'   => $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface'),
            'JhFlexiTime\ObjectManager'                  => $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            'JhFlexiTime\Service\PeriodService'          => $this->getMock('JhFlexiTime\Service\PeriodServiceInterface'),
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

        $factory = new BalanceServiceFactory();
        $this->assertInstanceOf('JhFlexiTime\Service\BalanceService', $factory->createService($serviceLocator));
    }
}
