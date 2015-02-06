<?php

namespace JhFlexiTimeTest\Controller\Factory;

use JhFlexiTime\Controller\Factory\UserReminderCliControllerFactory;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Controller\PluginManager;

/**
 * Class UserReminderCliControllerFactoryTest
 * @package JhFlexiTimeTest\Controller\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserReminderCliControllerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {

        $service = $this->getMockBuilder('\JhFlexiTime\Service\MissingBookingReminderService')
            ->disableOriginalConstructor()
            ->getMock();
        $services = [
            'JhFlexiTime\Service\MissingBookingReminderService' => $service,
            'Console' => $this->getMock('Zend\Console\Adapter\AdapterInterface')
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

        $factory = new UserReminderCliControllerFactory();
        $this->assertInstanceOf(
            'JhFlexiTime\Controller\UserReminderCliController',
            $factory->createService($controllerPluginManager)
        );
    }
}
