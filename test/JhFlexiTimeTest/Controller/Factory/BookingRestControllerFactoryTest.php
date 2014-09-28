<?php

namespace JhFlexiTimeTest\Controller\Factory;

use JhFlexiTime\Controller\Factory\BookingRestControllerFactory;
use Zend\Mvc\Controller\PluginManager;

/**
 * Class BookingRestControllerFactoryTest
 * @package JhFlexiTimeTest\Controller\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingRestControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {

        $bookingService = $this
            ->getMockBuilder('JhFlexiTime\Service\BookingService')
            ->disableOriginalConstructor()
            ->getMock();

        $timecalculatorService = $this
            ->getMockBuilder('JhFlexiTime\Service\TimeCalculatorService')
            ->disableOriginalConstructor()
            ->getMock();

        $userRepository = $this->getMock('JhUser\Repository\UserRepositoryInterface');

        $services = [
            'JhFlexiTime\Service\BookingService'        => $bookingService,
            'JhFlexiTime\Service\TimeCalculatorService' => $timecalculatorService,
            'JhUser\Repository\UserRepository'          => $userRepository,
        ];

        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
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

        $controllerPluginManager = new PluginManager();
        $controllerPluginManager->setServiceLocator($serviceLocator);

        $factory = new BookingRestControllerFactory();
        $this->assertInstanceOf(
            'JhFlexiTime\Controller\BookingRestController',
            $factory->createService($controllerPluginManager)
        );
    }
}
