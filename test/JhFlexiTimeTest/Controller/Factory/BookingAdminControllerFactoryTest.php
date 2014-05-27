<?php

namespace JhFlexiTimeTest\Controller\Factory;

use JhFlexiTime\Controller\Factory\BookingAdminControllerFactory;
use Zend\Mvc\Controller\PluginManager;
use Zend\View\HelperPluginManager;

/**
 * Class BookingAdminControllerFactoryTest
 * @package JhFlexiTimeTest\Controller\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingAdminControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {

        $bookingService = $this
            ->getMockBuilder('JhFlexiTime\Service\BookingService')
            ->disableOriginalConstructor()
            ->getMock();

        $timeCalculatorService = $this
            ->getMockBuilder('JhFlexiTime\Service\TimeCalculatorService')
            ->disableOriginalConstructor()
            ->getMock();

        $services = array(
            'JhFlexiTime\Service\BookingService'        => $bookingService,
            'JhFlexiTime\Service\TimeCalculatorService' => $timeCalculatorService,
            'JhUser\Repository\UserRepository'          => $this->getMock('JhUser\Repository\UserRepositoryInterface'),
        );

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

        $factory = new BookingAdminControllerFactory();
        $this->assertInstanceOf(
            'JhFlexiTime\Controller\BookingAdminController',
            $factory->createService($controllerPluginManager)
        );
    }
}
