<?php

namespace JhFlexiTimeTest\InputFilter\Factory;

use JhFlexiTime\InputFilter\Factory\BookingInputFilterFactory;
use Zend\InputFilter\InputFilterPluginManager;

/**
 * Class BookingInputFilterFactoryTest
 * @package JhFlexiTimeTest\Repository\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingInputFilterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsRepositoryFromObjectManager()
    {
        $services = [
            'JhFlexiTime\ObjectManager' =>
                $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            'JhFlexiTime\Repository\BookingRepository' =>
                $this->getMock('Doctrine\Common\Persistence\ObjectRepository'),
            'BookingOptions' =>
                $this->getMock('JhFlexiTime\Options\BookingOptionsInterface'),
            'JhUser\Repository\UserRepository' =>
                $this->getMock('Doctrine\Common\Persistence\ObjectRepository')
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

        $pluginLocator = new InputFilterPluginManager();
        $pluginLocator->setServiceLocator($serviceLocator);

        $factory = new BookingInputFilterFactory();
        $this->assertInstanceOf('JhFlexiTime\InputFilter\BookingInputFilter', $factory->createService($pluginLocator));
    }
}
