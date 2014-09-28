<?php

namespace JhFlexiTimeTest\Controller\Factory;

use JhFlexiTime\Controller\Factory\BookingControllerFactory;
use Zend\Mvc\Controller\PluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;

/**
 * Class BookingControllerFactoryTest
 * @package JhFlexiTimeTest\Controller\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingControllerFactoryTest extends \PHPUnit_Framework_TestCase
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

        $formElementManager = new ServiceManager();
        $formElementManager->setService('JhFlexiTime\Form\BookingForm', $this->getMock('Zend\Form\FormInterface'));

        $services = [
            'JhFlexiTime\Service\BookingService'        => $bookingService,
            'JhFlexiTime\Service\TimeCalculatorService' => $timeCalculatorService,
            'FormElementManager'                        => $formElementManager,
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

        $factory = new BookingControllerFactory();
        $this->assertInstanceOf(
            'JhFlexiTime\Controller\BookingController',
            $factory->createService($controllerPluginManager)
        );
    }
}
