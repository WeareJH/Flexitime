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
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');

        $service = $this->getMockBuilder('JhFlexiTime\Service\RunningBalanceService')
            ->disableOriginalConstructor()
            ->getMock();

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('JhFlexiTime\Service\RunningBalanceService')
            ->will($this->returnValue($service));

        $factory = new BookingSaveListenerFactory();
        $this->assertInstanceOf('JhFlexiTime\Listener\BookingSaveListener', $factory->createService($serviceLocator));
    }
}
