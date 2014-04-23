<?php

namespace JhFlexiTimeTest\Controller\Factory;

use JhFlexiTime\Controller\Factory\SettingsControllerFactory;
use Zend\Mvc\Controller\PluginManager;

/**
 * Class SettingsControllerFactoryTest
 * @package JhFlexiTimeTest\Controller\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class SettingsControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {

        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('BookingOptions')
            ->will($this->returnValue($this->getMock('JhFlexiTime\Options\BookingOptions')));

        $controllerPluginManager = new PluginManager();
        $controllerPluginManager->setServiceLocator($serviceLocator);

        $factory = new SettingsControllerFactory();
        $this->assertInstanceOf('JhFlexiTime\Controller\SettingsController', $factory->createService($controllerPluginManager));
    }
} 