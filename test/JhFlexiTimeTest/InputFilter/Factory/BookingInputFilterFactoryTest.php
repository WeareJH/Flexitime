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
        $authService = $this->getMock('\Zend\Authentication\AuthenticationServiceInterface');
        $authService
            ->expects($this->once())
            ->method('getIdentity')
            ->will($this->returnValue($this->getMock('ZfcUser\Entity\UserInterface')));

        $services = array(
            'JhFlexiTime\ObjectManager' =>
                $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
            'JhFlexiTime\Repository\BookingRepository' =>
                $this->getMock('Doctrine\Common\Persistence\ObjectRepository'),
            'zfcuser_auth_service' => $authService,
            'BookingOptions' =>
                $this->getMock('JhFlexiTime\Options\BookingOptionsInterface'),
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

        $pluginLocator = new InputFilterPluginManager();
        $pluginLocator->setServiceLocator($serviceLocator);

        $factory = new BookingInputFilterFactory();
        $this->assertInstanceOf('JhFlexiTime\InputFilter\BookingInputFilter', $factory->createService($pluginLocator));
    }
}
