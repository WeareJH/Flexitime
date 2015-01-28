<?php

namespace JhFlexiTimeTest\Listener\Factory;

use JhFlexiTime\Listener\Factory\BookingSaveListenerFactory;
use JhFlexiTime\Listener\Factory\CappedCreditListenerFactory;
use JhFlexiTime\Options\ModuleOptions;

/**
 * Class CappedCreditListenerFactoryTest
 * @package JhFlexiTimeTest\Service\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditListenerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $service = $this->getMockBuilder('JhFlexiTime\Service\CappedCreditService')
            ->disableOriginalConstructor()
            ->getMock();

        $serviceLocator
            ->expects($this->at(0))
            ->method('get')
            ->with('JhFlexiTime\Service\CappedCreditService')
            ->will($this->returnValue($service));

        $serviceLocator
            ->expects($this->at(1))
            ->method('get')
            ->with('FlexiOptions')
            ->will($this->returnValue(new ModuleOptions));

        $factory = new CappedCreditListenerFactory();
        $this->assertInstanceOf('JhFlexiTime\Listener\CappedCreditListener', $factory->createService($serviceLocator));
    }
}
