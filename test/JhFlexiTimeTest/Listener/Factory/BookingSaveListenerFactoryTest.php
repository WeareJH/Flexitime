<?php

namespace JhFlexiTimeTest\Listener\Factory;

use JhFlexiTime\Listener\BookingSaveListener;
use JhFlexiTime\Listener\Factory\BookingSaveListenerFactory;

/**
 * Class BookingSaveListenerFactoryTest
 * @package JhFlexiTimeTest\Service\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingSaveListenerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {

        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services         = [
            'JhFlexiTime\ObjectManager' =>
                $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            'JhFlexiTime\Repository\BalanceRepository' =>
                $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface'),
            'FlexiOptions' =>
                $this->getMock('JhFlexiTime\Options\ModuleOptions'),
            'JhFlexiTime\Repository\UserSettingsRepository' =>
                $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface')
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

        $factory = new BookingSaveListenerFactory();
        $this->assertInstanceOf('JhFlexiTime\Listener\BookingSaveListener', $factory->createService($serviceLocator));
    }
}
