<?php

namespace JhFlexiTimeTest;

use JhFlexiTime\Module;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ModuleTest
 * @package JhFlexiTimeTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceLocator;

    /**
     * @var \Zend\EventManager\EventManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    public function testGetConfig()
    {
        $module = new Module();

        $this->assertInternalType('array', $module->getConfig());
        $this->assertSame($module->getConfig(), unserialize(serialize($module->getConfig())), 'Config is serializable');
    }

    public function testGetAutoloaderConfig()
    {
        $module = new Module;
        $this->assertInternalType('array', $module->getAutoloaderConfig());
    }

    public function testListenersAreRegistered()
    {
        $event = $this->getEvent();
        $module = new Module();

        $bookingSaveListener = $this->getMockBuilder('JhFlexiTime\Listener\BookingSaveListener')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceLocator->setService('JhFlexiTime\Listener\BookingSaveListener', $bookingSaveListener);
        $this->eventManager
            ->expects($this->once())
            ->method('attach')
            ->with($bookingSaveListener);


        $module->onBootstrap($event);
    }

    /**
     * @return \Zend\EventManager\EventInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEvent()
    {
        $this->serviceLocator = new ServiceManager();
        $this->eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $application = $this->getMock('Zend\Mvc\Application', [], [], '', false);

        $application->expects($this->any())
            ->method('getServiceManager')
            ->will($this->returnValue($this->serviceLocator));

        $application->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($this->eventManager));

        $event = $this->getMock('Zend\EventManager\EventInterface');
        $event->expects($this->any())->method('getTarget')->will($this->returnValue($application));

        return $event;
    }

}