<?php

namespace JhFlexiTimeTest\Service\Factory;

use JhFlexiTime\Service\Factory\BookingServiceFactory;

/**
 * Class BookingServiceFactoryTest
 * @package JhFlexiTimeTest\Service\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {

        $inputFilter = $this->getMock('Zend\InputFilter\InputFilterInterface');

        $FilterPluginManager    = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $FilterPluginManager->expects($this->once())
            ->method('get')
            ->with('JhFlexiTime\InputFilter\BookingInputFilter')
            ->will($this->returnValue($inputFilter));


        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services         = array(
            'FlexiOptions'                              => $this->getMock('JhFlexiTime\Options\ModuleOptions'),
            'JhFlexiTime\Repository\BookingRepository'  => $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface'),
            'JhFlexiTime\ObjectManager'                 => $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            'JhFlexiTime\Service\PeriodService'         => $this->getMock('JhFlexiTime\Service\PeriodServiceInterface'),
            'InputFilterManager'                        => $FilterPluginManager
        );

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

        $factory = new BookingServiceFactory();
        $this->assertInstanceOf('JhFlexiTime\Service\BookingService', $factory->createService($serviceLocator));
    }
}
