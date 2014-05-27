<?php

namespace JhFlexiTimeTest\Service\Factory;

use JhFlexiTime\Service\Factory\RunningBalanceServiceFactory;

/**
 * Class RunningBalanceServiceFactoryTest
 * @package JhFlexiTimeTest\Service\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class RunningBalanceServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services         = [
            'JhUser\Repository\UserRepository' =>
                $this->getMock('JhUser\Repository\UserRepositoryInterface'),
            'JhFlexiTime\Repository\UserSettingsRepository' =>
                $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface'),
            'JhFlexiTime\Repository\BookingRepository' =>
                $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface'),
            'JhFlexiTime\Repository\BalanceRepository' =>
                $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface'),
            'JhFlexiTime\Service\PeriodService' =>
                $this->getMock('JhFlexiTime\Service\PeriodServiceInterface'),
            'JhFlexiTime\ObjectManager' =>
                $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
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

        $factory = new RunningBalanceServiceFactory();
        $this->assertInstanceOf('JhFlexiTime\Service\RunningBalanceService', $factory->createService($serviceLocator));
    }
}
